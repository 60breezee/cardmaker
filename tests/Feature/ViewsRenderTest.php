<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ViewsRenderTest extends TestCase
{
    use RefreshDatabase;

    private function template(): Template
    {
        return Template::create([
            'name' => 'Professionnel',
            'slug' => 'professionnel-'.uniqid(),
            'configuration' => [
                'width' => 1050,
                'height' => 600,
                'background' => '#0a241a',
                'elements' => [
                    ['type' => 'text', 'field' => 'full_name', 'x' => 60, 'y' => 60, 'width' => 400, 'height' => 50],
                    ['type' => 'qr_code', 'x' => 820, 'y' => 120, 'width' => 140, 'height' => 140],
                ],
            ],
        ]);
    }

    public function test_public_pages_render(): void
    {
        $template = $this->template();
        $card = User::factory()->create()->cards()->create(['template_id' => $template->id, 'name' => 'Publique', 'data' => ['full_name' => 'Alice'], 'is_public' => true]);

        $this->get(route('welcome'))->assertOk();
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
        $this->get(route('password.request'))->assertOk();
        $this->get(route('cards.verify', $card->public_identifier))->assertOk()->assertSee('Carte vérifiée');
    }

    public function test_authenticated_pages_render(): void
    {
        $template = $this->template();
        $user = User::factory()->create();
        $card = $user->cards()->create(['template_id' => $template->id, 'name' => 'Ma carte', 'data' => ['full_name' => 'Alice']]);
        $user->bulkGenerations()->create(['template_id' => $template->id, 'csv_path' => 'bulk/x.csv', 'status' => 'completed', 'total' => 2, 'processed' => 2, 'zip_path' => 'bulk/x.zip']);
        $user->notifications()->create(['id' => (string) Str::ulid(), 'type' => 'App\Notifications\CardGeneratedNotification', 'data' => ['title' => 'Export', 'message' => 'Prêt', 'card_id' => $card->id]]);

        $this->actingAs($user);
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('cards.index'))->assertOk();
        $this->get(route('cards.create', ['template' => $template->id]))->assertOk();
        $this->get(route('cards.edit', $card))->assertOk();
        $this->get(route('cards.history', $card))->assertOk();
        $this->get(route('templates.index'))->assertOk();
        $this->get(route('billing.index'))->assertOk();
        $this->get(route('notifications.index'))->assertOk();
        $this->get(route('profile.edit'))->assertOk();
        $this->get(route('bulk.create'))->assertOk();
    }

    public function test_admin_pages_render(): void
    {
        $template = $this->template();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);
        $this->get(route('admin.index'))->assertOk();
        $this->get(route('admin.templates.index'))->assertOk();
        $this->get(route('admin.templates.edit', $template))->assertOk();
        $this->get(route('admin.templates.create'))->assertOk();
        $this->get(route('admin.users.index'))->assertOk();
    }

    public function test_billing_checkout_is_graceful_without_payment_provider(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->post(route('billing.checkout'), ['plan' => 'pro', 'currency' => 'XOF'])
            ->assertSessionHas('error');
    }
}
