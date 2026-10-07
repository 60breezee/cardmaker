<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WalkthroughTest extends TestCase
{
    use RefreshDatabase;

    private function template(): Template
    {
        return Template::create([
            'name' => 'Pro',
            'slug' => 'walk-pro',
            'description' => 'Carte sombre',
            'category' => 'professional',
            'is_premium' => false,
            'is_active' => true,
            'configuration' => [
                'width' => 1050,
                'height' => 600,
                'background' => '#0b1220',
                'elements' => [
                    ['type' => 'shape', 'x' => 0, 'y' => 0, 'width' => 1050, 'height' => 12, 'color' => '#13C878', 'z_index' => 0],
                    ['type' => 'logo', 'field' => 'logo', 'x' => 60, 'y' => 34, 'width' => 130, 'height' => 52, 'z_index' => 2],
                    ['type' => 'photo', 'field' => 'photo', 'x' => 80, 'y' => 360, 'width' => 190, 'height' => 200, 'z_index' => 2],
                    ['type' => 'text', 'field' => 'full_name', 'x' => 330, 'y' => 170, 'width' => 480, 'height' => 44, 'color' => '#f2f6fa', 'z_index' => 3],
                    ['type' => 'text', 'field' => 'email', 'x' => 330, 'y' => 360, 'width' => 380, 'height' => 18, 'color' => '#a7b7c8', 'z_index' => 3],
                    ['type' => 'qr_code', 'x' => 800, 'y' => 340, 'width' => 180, 'height' => 180, 'z_index' => 4],
                ],
            ],
        ]);
    }

    public function test_member_full_walk_through(): void
    {
        Storage::fake('local');
        $template = $this->template();

        $this->get(route('welcome'))->assertOk();
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();

        $this->post(route('register'), ['name' => 'Florent', 'email' => 'florent@test.dev', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->actingAs($user = User::where('email', 'florent@test.dev')->firstOrFail());

        $this->get(route('dashboard'))->assertOk()->assertSee('Templates');
        $this->get(route('templates.index'))->assertOk()->assertSee($template->name);
        $this->get(route('cards.create'))->assertOk()->assertSee('Nombre de cartes');

        $this->post(route('cards.store'), ['template_id' => $template->id, 'name' => 'Carte Florent', 'quantity' => 1, 'data' => ['full_name' => 'Florent', 'email' => 'florent@test.dev']])
            ->assertRedirect();
        $card = $user->cards()->firstOrFail();
        $this->assertNotEmpty($card->data['identifier']);

        $this->get(route('cards.edit', $card))->assertOk()->assertSee('cardEditor')->assertSee('Générer');
        $this->post(route('cards.upload', $card), ['photo' => UploadedFile::fake()->image('photo.jpg', 600, 400)])->assertRedirect();
        $this->post(route('cards.upload-asset', [$card, 'logo']), ['logo' => UploadedFile::fake()->image('logo.png', 300, 300)])->assertRedirect();
        $this->assertNotEmpty($card->fresh()->data['photo']);
        $this->assertNotEmpty($card->fresh()->data['logo']);

        $this->put(route('cards.update', $card), ['name' => 'Carte Florent', 'data' => ['full_name' => 'Florent', 'email' => 'florent@test.dev', 'identifier' => $card->data['identifier']], 'is_public' => '1'])
            ->assertRedirect();

        foreach (['png', 'jpg'] as $format) {
            $this->post(route('cards.generate', $card), ['format' => $format])->assertRedirect();
            $export = $card->exports()->where('format', strtoupper($format))->latest()->firstOrFail();
            Storage::disk('local')->assertExists($export->file_path);
            $this->get(route('cards.download', [$card, $format]))->assertOk();
        }
        $this->post(route('cards.generate', $card), ['format' => 'pdf'])->assertRedirect();

        $this->post(route('cards.duplicate', $card))->assertRedirect();
        $this->assertSame(2, $user->cards()->count());

        $this->get(route('cards.index'))->assertOk()->assertSee($card->name);
        $this->get(route('cards.history', $card))->assertOk()->assertSee('CARD CREATED');

        $this->get(route('cards.verify', $card->public_identifier))->assertOk()->assertSee('Carte vérifiée')->assertSee($card->public_identifier);
        $this->get(route('cards.share', $card->public_identifier))->assertOk();
        $this->get(route('cards.asset', [$card, 'photo']))->assertOk();

        $this->get(route('notifications.index'))->assertOk();
        $notification = $card->user->notifications()->firstOrFail();
        $this->patch(route('notifications.read', $notification->getKey()))->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);

        $this->get(route('billing.index'))->assertOk()->assertSee('Bientôt disponible');
        $this->post(route('billing.checkout'), ['plan' => 'pro', 'currency' => 'XOF'])->assertSessionHas('error');

        $this->put(route('profile.update'), ['name' => 'Florent Nouveau', 'email' => 'florent@test.dev'])->assertRedirect();
        $this->put(route('profile.password'), ['current_password' => 'password', 'password' => 'newpassword', 'password_confirmation' => 'newpassword'])->assertRedirect();

        $this->get(route('bulk.create'))->assertOk();
        $this->post(route('bulk.store'), ['template_id' => $template->id, 'csv' => UploadedFile::fake()->createWithContent('c.csv', "nom\nAlice\n")])
            ->assertSessionHasErrors('quota');

        $this->delete(route('cards.destroy', $card))->assertRedirect(route('cards.index'));
        $this->assertSame(1, $user->cards()->count());
    }

    public function test_business_bulk_journey(): void
    {
        Storage::fake('local');
        $template = $this->template();
        $user = User::factory()->create();
        $user->subscriptions()->create(['plan' => 'business', 'status' => 'active', 'started_at' => now()]);

        $this->actingAs($user);
        $this->get(route('bulk.create'))->assertOk()->assertSee($template->name);

        $headers = "nom;fonction;email;telephone;entreprise\nAlice;Directrice;alice@x.com;+22177000000;ACME\nBob;Développeur;bob@x.com;+22177000001;ACME\n";
        $this->post(route('bulk.store'), ['template_id' => $template->id, 'csv' => UploadedFile::fake()->createWithContent('people.csv', $headers)])
            ->assertRedirect();

        $generation = $user->bulkGenerations()->firstOrFail()->refresh();
        $this->assertSame('completed', $generation->status);
        $this->assertSame(2, $user->cards()->count());
        $this->get(route('bulk.download', $generation))->assertOk();

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_premium_templates_are_hidden_from_free_users(): void
    {
        $template = $this->template();
        $premium = Template::create(['name' => 'Cobalt', 'slug' => 'walk-cobalt', 'is_premium' => true, 'is_active' => true, 'configuration' => $template->configuration]);
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->get(route('templates.index'))->assertDontSee($premium->name)->assertSee($template->name);
        $this->get(route('cards.create', ['template' => $premium->id]))->assertNotFound();
        $this->get(route('dashboard'))->assertDontSee($premium->name);
    }
}
