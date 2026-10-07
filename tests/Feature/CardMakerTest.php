<?php

namespace Tests\Feature;

use App\Jobs\GenerateBulkCardsJob;
use App\Models\ActivityLog;
use App\Models\Template;
use App\Models\User;
use App\Services\CardGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CardMakerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_generate_a_card(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $template = Template::create(['name' => 'Test', 'slug' => 'test', 'configuration' => ['width' => 400, 'height' => 240, 'background' => '#ffffff', 'elements' => [['type' => 'text', 'field' => 'full_name', 'x' => 20, 'y' => 20]]]]);
        $card = $user->cards()->create(['template_id' => $template->id, 'name' => 'Carte test', 'data' => ['full_name' => 'Alice']]);
        $export = app(CardGeneratorService::class)->generate($card);
        $this->assertSame('PNG', $export->format);
        Storage::disk('local')->assertExists($export->file_path);
    }

    public function test_user_cannot_view_another_users_card(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $template = Template::create(['name' => 'Test', 'slug' => 'test-2', 'configuration' => ['width' => 400, 'height' => 240, 'elements' => []]]);
        $card = $owner->cards()->create(['template_id' => $template->id, 'name' => 'Privée', 'data' => []]);
        $this->actingAs($other)->get(route('cards.edit', $card))->assertForbidden();
    }

    public function test_user_can_generate_a_pdf_export(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $template = Template::create(['name' => 'PDF', 'slug' => 'pdf', 'configuration' => ['width' => 400, 'height' => 240, 'elements' => []]]);
        $card = $user->cards()->create(['template_id' => $template->id, 'name' => 'PDF test', 'data' => ['full_name' => 'Alice']]);
        $this->actingAs($user)->post(route('cards.generate', $card), ['format' => 'pdf'])->assertRedirect();
        $this->assertDatabaseHas('card_exports', ['card_id' => $card->id, 'format' => 'PDF']);
        $export = $card->exports()->where('format', 'PDF')->latest()->firstOrFail();
        Storage::disk('local')->assertExists($export->file_path);
    }

    public function test_owner_can_open_the_reactive_editor(): void
    {
        $user = User::factory()->create();
        $template = Template::create(['name' => 'Editor', 'slug' => 'editor', 'configuration' => ['width' => 1050, 'height' => 600, 'elements' => []]]);
        $card = $user->cards()->create(['template_id' => $template->id, 'name' => 'Editor test', 'data' => ['full_name' => 'Alice']]);
        $this->actingAs($user)->get(route('cards.edit', $card))->assertOk()->assertSee('cardEditor');
    }

    public function test_owner_can_upload_a_logo_and_invalid_files_are_rejected(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $template = Template::create(['name' => 'Upload', 'slug' => 'upload', 'configuration' => ['width' => 400, 'height' => 240, 'elements' => []]]);
        $card = $user->cards()->create(['template_id' => $template->id, 'name' => 'Upload test', 'data' => []]);
        $this->actingAs($user)->post(route('cards.upload-asset', [$card, 'logo']), ['logo' => UploadedFile::fake()->image('logo.png', 300, 300)])->assertRedirect();
        $this->assertNotEmpty($card->fresh()->data['logo']);
        $this->actingAs($user)->post(route('cards.upload-asset', [$card, 'logo']), ['logo' => UploadedFile::fake()->create('malware.php', 10, 'application/x-php')])->assertSessionHasErrors('logo');
    }

    public function test_card_assets_are_private_to_the_owner(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $template = Template::create(['name' => 'Asset', 'slug' => 'asset', 'configuration' => ['width' => 400, 'height' => 240, 'elements' => []]]);
        Storage::disk('local')->put('uploads/'.$owner->id.'/photo.png', 'image-content');
        $card = $owner->cards()->create(['template_id' => $template->id, 'name' => 'Private asset', 'data' => ['photo' => 'uploads/'.$owner->id.'/photo.png']]);

        $this->actingAs($owner)->get(route('cards.asset', [$card, 'photo']))->assertOk();
        $this->actingAs($other)->get(route('cards.asset', [$card, 'photo']))->assertForbidden();
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.index'))->assertForbidden();
    }

    public function test_admin_can_access_template_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.templates.index'))->assertOk();
    }

    public function test_owner_can_view_card_history_but_other_users_cannot(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $template = Template::create(['name' => 'History', 'slug' => 'history', 'configuration' => ['width' => 400, 'height' => 240, 'elements' => []]]);
        $card = $owner->cards()->create(['template_id' => $template->id, 'name' => 'Historique test', 'data' => []]);
        ActivityLog::create(['user_id' => $owner->id, 'event' => 'CARD_CREATED', 'subject_type' => $card->getMorphClass(), 'subject_id' => $card->id, 'metadata' => []]);

        $this->actingAs($owner)->get(route('cards.history', $card))->assertOk()->assertSee('CARD CREATED');
        $this->actingAs($other)->get(route('cards.history', $card))->assertForbidden();
    }

    public function test_bulk_generation_is_restricted_by_plan(): void
    {
        $user = User::factory()->create();
        $template = Template::create(['name' => 'Bulk', 'slug' => 'bulk', 'configuration' => ['width' => 400, 'height' => 240, 'elements' => []]]);

        $this->actingAs($user)
            ->post(route('bulk.store'), ['template_id' => $template->id, 'csv' => UploadedFile::fake()->createWithContent('cards.csv', "nom\nAlice\n")])
            ->assertSessionHasErrors('quota');
    }

    public function test_free_user_cannot_create_a_premium_card(): void
    {
        $user = User::factory()->create();
        $template = Template::create(['name' => 'Premium', 'slug' => 'premium', 'is_premium' => true, 'configuration' => ['width' => 400, 'height' => 240, 'elements' => []]]);

        $this->actingAs($user)
            ->post(route('cards.store'), ['template_id' => $template->id, 'name' => 'Premium card', 'data' => []])
            ->assertSessionHasErrors('template_id');

        $this->assertDatabaseMissing('cards', ['name' => 'Premium card']);
    }

    public function test_inactive_template_cannot_be_used(): void
    {
        $user = User::factory()->create();
        $template = Template::create(['name' => 'Inactive', 'slug' => 'inactive', 'is_active' => false, 'configuration' => ['width' => 400, 'height' => 240, 'elements' => []]]);

        $this->actingAs($user)
            ->post(route('cards.store'), ['template_id' => $template->id, 'name' => 'Inactive card', 'data' => []])
            ->assertSessionHasErrors('template_id');
    }

    public function test_card_generation_rejects_unknown_formats(): void
    {
        $user = User::factory()->create();
        $template = Template::create(['name' => 'Format', 'slug' => 'format', 'configuration' => ['width' => 400, 'height' => 240, 'elements' => []]]);
        $card = $user->cards()->create(['template_id' => $template->id, 'name' => 'Format test', 'data' => []]);

        $this->actingAs($user)
            ->post(route('cards.generate', $card), ['format' => 'svg'])
            ->assertSessionHasErrors('format');
    }

    public function test_api_user_can_create_and_revoke_a_token(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $response = $this->postJson('/api/v1/auth/token', ['email' => $user->email, 'password' => 'password', 'device_name' => 'test-device']);

        $response->assertOk()->assertJsonStructure(['token', 'user']);
        $token = $response->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/auth/token')
            ->assertOk()
            ->assertJson(['message' => 'Token révoqué.']);
    }

    public function test_qr_payload_contains_all_card_information(): void
    {
        $card = User::factory()->create()->cards()->create([
            'template_id' => Template::create(['name' => 'QR', 'slug' => 'qr', 'configuration' => ['width' => 400, 'height' => 240, 'elements' => []]])->id,
            'name' => 'QR test',
            'data' => ['full_name' => 'Alice Dupont', 'company' => 'ACME', 'job_title' => 'Directrice', 'email' => 'alice@example.com', 'phone' => '+22177000000', 'identifier' => 'CM-ABC123'],
        ]);

        $payload = app(CardGeneratorService::class)->qrPayload($card);

        $this->assertStringContainsString('BEGIN:VCARD', $payload);
        $this->assertStringContainsString('FN:Alice Dupont', $payload);
        $this->assertStringContainsString('ORG:ACME', $payload);
        $this->assertStringContainsString('TITLE:Directrice', $payload);
        $this->assertStringContainsString('TEL;TYPE=CELL:+22177000000', $payload);
        $this->assertStringContainsString('EMAIL:alice@example.com', $payload);
        $this->assertStringContainsString('IDENTIFIANT CM-ABC123', $payload);
        $this->assertStringContainsString('/verify/'.$card->public_identifier, $payload);
        $this->assertStringContainsString('END:VCARD', $payload);
    }

    public function test_user_can_create_several_cards_at_once(): void
    {
        $user = User::factory()->create();
        $template = Template::create(['name' => 'Multi', 'slug' => 'multi', 'configuration' => ['width' => 400, 'height' => 240, 'elements' => []]]);

        $this->actingAs($user)
            ->post(route('cards.store'), ['template_id' => $template->id, 'name' => 'Pack', 'quantity' => 3, 'data' => ['full_name' => 'Alice']])
            ->assertRedirect();

        $cards = $user->cards()->orderBy('id')->get();
        $this->assertCount(3, $cards);
        $this->assertSame(['Pack', 'Pack (2)', 'Pack (3)'], $cards->pluck('name')->all());
        $this->assertCount(3, $cards->pluck('public_identifier')->unique());
        $this->assertCount(3, $cards->pluck('data')->map(fn ($data) => $data['identifier'])->unique());
    }

    public function test_quantity_outside_limit_is_rejected(): void
    {
        $user = User::factory()->create();
        $template = Template::create(['name' => 'Quota', 'slug' => 'quota', 'configuration' => ['width' => 400, 'height' => 240, 'elements' => []]]);

        $this->actingAs($user)
            ->post(route('cards.store'), ['template_id' => $template->id, 'name' => 'Pack', 'quantity' => 21, 'data' => []])
            ->assertSessionHasErrors('quantity');
        $this->assertSame(0, $user->cards()->count());
    }

    public function test_public_card_assets_are_served_to_guests(): void
    {
        Storage::fake('local');
        $template = Template::create(['name' => 'Public', 'slug' => 'public', 'configuration' => ['width' => 400, 'height' => 240, 'elements' => []]]);
        Storage::disk('local')->put('uploads/1/photo.png', 'image-content');
        $owner = User::factory()->create();
        $publicCard = $owner->cards()->create(['template_id' => $template->id, 'name' => 'Public', 'data' => ['photo' => 'uploads/1/photo.png'], 'is_public' => true]);
        $privateCard = $owner->cards()->create(['template_id' => $template->id, 'name' => 'Privée', 'data' => ['photo' => 'uploads/1/photo.png'], 'is_public' => false]);

        $this->get(route('cards.asset', [$publicCard, 'photo']))->assertOk();
        $this->get(route('cards.asset', [$privateCard, 'photo']))->assertForbidden();
    }

    public function test_bulk_job_maps_csv_columns_and_generates_zip(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $user->subscriptions()->create(['plan' => 'business', 'status' => 'active', 'started_at' => now()]);
        $template = Template::create(['name' => 'Bulk', 'slug' => 'bulk-ok', 'configuration' => ['width' => 400, 'height' => 240, 'background' => '#ffffff', 'elements' => [['type' => 'text', 'field' => 'full_name', 'x' => 20, 'y' => 20]]]]);
        $csv = UploadedFile::fake()->createWithContent('cards.csv', "nom;prenom;fonction;email;telephone;entreprise\nAlice;Dupont;Directrice;alice@example.com;+22177000000;ACME\n");
        $path = $csv->store('bulk');
        $generation = $user->bulkGenerations()->create(['template_id' => $template->id, 'csv_path' => $path]);

        GenerateBulkCardsJob::dispatchSync($user->id, $template->id, $path, $generation->id);

        $card = $user->cards()->firstOrFail();
        $this->assertSame('Alice Dupont', $card->data['full_name']);
        $this->assertSame('Directrice', $card->data['job_title']);
        $this->assertSame('ACME', $card->data['company']);
        $this->assertSame('+22177000000', $card->data['phone']);
        $this->assertNotEmpty($card->data['identifier']);
        $generation->refresh();
        $this->assertSame('completed', $generation->status);
        $this->assertNotEmpty($generation->zip_path);
        Storage::disk('local')->assertExists($generation->zip_path);
    }
}
