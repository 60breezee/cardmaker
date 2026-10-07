<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demo = User::factory()->create(['name' => 'Demo CardMaker', 'email' => 'demo@cardmaker.test']);
        User::factory()->create(['name' => 'Admin CardMaker', 'email' => 'admin@cardmaker.test', 'role' => 'admin']);
        foreach ([['Professional', 'professional'], ['Business', 'business'], ['Member', 'member'], ['Student', 'student'], ['Event', 'event']] as [$name, $category]) {
            Template::create(['name' => $name, 'slug' => Str::slug($name), 'description' => 'Template fictif '.$name.' pour cartes personnalisées.', 'category' => $category, 'configuration' => ['width' => 1050, 'height' => 600, 'background' => '#f8fafc', 'elements' => [['type' => 'shape', 'x' => 0, 'y' => 0, 'width' => 1050, 'height' => 110, 'color' => '#0f172a', 'z_index' => 0], ['type' => 'text', 'field' => 'full_name', 'x' => 70, 'y' => 180, 'color' => '#0f172a', 'z_index' => 2], ['type' => 'text', 'field' => 'job_title', 'x' => 70, 'y' => 220, 'color' => '#475569', 'z_index' => 2], ['type' => 'text', 'field' => 'company', 'x' => 70, 'y' => 270, 'color' => '#475569', 'z_index' => 2], ['type' => 'qr_code', 'x' => 820, 'y' => 350, 'width' => 160, 'height' => 160, 'z_index' => 3]]], 'is_premium' => false, 'is_active' => true, 'created_by' => $demo->id]);
        }
    }
}
