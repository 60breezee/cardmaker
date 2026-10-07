<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function configuration(array $elements = []): string
    {
        return json_encode([
            'width' => 1050,
            'height' => 600,
            'background' => '#0b1220',
            'elements' => $elements ?: [
                ['type' => 'qr_code', 'x' => 830, 'y' => 120, 'width' => 160, 'height' => 160],
                ['type' => 'text', 'field' => 'full_name', 'x' => 270, 'y' => 140, 'width' => 540, 'height' => 48, 'font_size' => 36],
            ],
        ]);
    }

    public function test_user_can_create_a_custom_template(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('templates.store'), ['name' => 'Ma carte', 'configuration' => $this->configuration()])
            ->assertRedirect();

        $template = Template::where('created_by', $user->id)->firstOrFail();
        $this->assertSame('Ma carte', $template->name);
        $this->assertSame('custom', $template->category);
        $this->assertTrue($template->is_active);
        $this->assertFalse($template->is_premium);
        $this->assertSame($user->id, $template->created_by);
        $this->assertSame(1050, $template->configuration['width']);
        $this->assertSame(600, $template->configuration['height']);
        $this->assertSame('#0b1220', $template->configuration['background']);
    }

    public function test_redirect_to_card_creation_after_saving(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('templates.store'), ['name' => 'Ma carte', 'configuration' => $this->configuration()])
            ->assertRedirectToRoute('cards.create', ['template' => Template::where('created_by', $user->id)->value('id')])
            ->assertSessionHas('success');
    }

    public function test_guest_cannot_access_the_builder(): void
    {
        $this->get(route('templates.builder'))->assertRedirectToRoute('login');
        $this->post(route('templates.store'), ['name' => 'X', 'configuration' => $this->configuration()])->assertRedirectToRoute('login');
    }

    public function test_authenticated_user_can_open_the_builder(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('templates.builder'))->assertOk()->assertSee('templateBuilder');
    }

    public function test_invalid_configuration_json_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('templates.store'), ['name' => 'X', 'configuration' => '{pas du json'])
            ->assertSessionHasErrors('configuration');

        $this->assertDatabaseMissing('templates', ['name' => 'X']);
    }

    public function test_configuration_must_contain_at_least_one_element(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('templates.store'), ['name' => 'X', 'configuration' => json_encode(['width' => 1050, 'height' => 600, 'elements' => []])])
            ->assertSessionHasErrors('configuration.elements');
    }

    public function test_unknown_element_keys_are_stripped(): void
    {
        $user = User::factory()->create();
        $config = json_encode([
            'width' => 1050,
            'height' => 600,
            'elements' => [
                ['type' => 'text', 'field' => 'full_name', 'x' => 10, 'y' => 20, 'width' => 100, 'height' => 30, 'evil' => 'dropped', 'onclick' => 'dropped'],
            ],
        ]);

        $this->actingAs($user)->post(route('templates.store'), ['name' => 'X', 'configuration' => $config])->assertRedirect();

        $element = Template::where('created_by', $user->id)->firstOrFail()->configuration['elements'][0];
        $this->assertArrayNotHasKey('evil', $element);
        $this->assertArrayNotHasKey('onclick', $element);
        $this->assertSame(['type', 'field', 'x', 'y', 'width', 'height'], array_keys($element));
    }

    public function test_custom_template_is_only_visible_to_its_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($owner)->post(route('templates.store'), ['name' => 'Modèle secret', 'configuration' => $this->configuration()]);

        $this->actingAs($owner)->get(route('templates.index'))->assertSee('Modèle secret')->assertSee('Mes modèles');

        $this->actingAs($other)->get(route('templates.index'))->assertDontSee('Modèle secret');
        $this->actingAs($other)->get(route('templates.index'))->assertDontSee('Mes modèles');
    }

    public function test_custom_template_is_hidden_from_admin_template_list(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $custom = $owner->templates()->create(['name' => 'Perso', 'slug' => 'perso', 'category' => 'custom', 'is_active' => true, 'configuration' => ['width' => 1050, 'height' => 600, 'elements' => []]]);
        Template::create(['name' => 'Global', 'slug' => 'global', 'is_active' => true, 'configuration' => ['width' => 1050, 'height' => 600, 'elements' => []]]);

        $this->actingAs($admin)->get(route('admin.templates.index'))->assertSee('Global')->assertDontSee('Perso');

        $this->assertDatabaseHas('templates', ['id' => $custom->id]);
    }

    public function test_another_user_cannot_use_a_custom_template(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $template = $owner->templates()->create(['name' => 'Perso', 'slug' => 'perso-2', 'category' => 'custom', 'is_active' => true, 'configuration' => ['width' => 1050, 'height' => 600, 'elements' => []]]);

        $this->actingAs($other)
            ->post(route('cards.store'), ['template_id' => $template->id, 'name' => 'Vol', 'data' => []])
            ->assertSessionHasErrors('template_id');

        $this->assertSame(0, $other->cards()->count());

        $this->actingAs($other)->get(route('cards.create', ['template' => $template->id]))->assertNotFound();
    }

    public function test_owner_can_create_a_card_from_their_custom_template(): void
    {
        $owner = User::factory()->create();
        $template = $owner->templates()->create(['name' => 'Perso', 'slug' => 'perso-3', 'category' => 'custom', 'is_active' => true, 'configuration' => ['width' => 1050, 'height' => 600, 'elements' => [['type' => 'qr_code', 'x' => 0, 'y' => 0, 'width' => 100, 'height' => 100]]]]);

        $this->actingAs($owner)
            ->post(route('cards.store'), ['template_id' => $template->id, 'name' => 'Ma carte', 'data' => ['full_name' => 'Alice']])
            ->assertRedirect();

        $card = $owner->cards()->firstOrFail();
        $this->assertSame($template->id, $card->template_id);
    }

    public function test_duplicate_slugs_get_a_random_suffix(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post(route('templates.store'), ['name' => 'Mon modèle', 'configuration' => $this->configuration()]);
        $this->actingAs($owner)->post(route('templates.store'), ['name' => 'Mon modèle', 'configuration' => $this->configuration()]);

        $slugs = Template::where('created_by', $owner->id)->pluck('slug')->all();
        $this->assertCount(2, $slugs);
        $this->assertSame(count($slugs), count(array_unique($slugs)));
    }
}
