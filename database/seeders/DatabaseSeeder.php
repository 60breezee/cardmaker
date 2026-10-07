<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demo = User::firstOrCreate(
            ['email' => 'demo@cardmaker.test'],
            ['name' => 'Demo CardMaker', 'password' => 'password']
        );
        User::firstOrCreate(
            ['email' => 'admin@cardmaker.test'],
            ['name' => 'Admin CardMaker', 'password' => 'password', 'role' => 'admin']
        );

        foreach (self::presets() as $preset) {
            Template::updateOrCreate(['slug' => $preset['slug']], [
                'name' => $preset['name'],
                'description' => $preset['description'],
                'category' => $preset['category'],
                'configuration' => $preset['configuration'],
                'is_premium' => $preset['is_premium'],
                'is_active' => true,
                'created_by' => $demo->id,
            ]);
        }
    }

    /**
     * Blueprints partagés entre le seeder et les mises à jour de configuration.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function presets(): array
    {
        return [
            [
                'slug' => 'professional',
                'name' => 'Pro',
                'description' => 'Carte sombre sobre avec bandeau accent, photo, coordonnées et QR.',
                'category' => 'professional',
                'is_premium' => false,
                'configuration' => [
                    'width' => 1050,
                    'height' => 600,
                    'background' => '#0b1220',
                    'elements' => [
                        ['type' => 'shape', 'x' => 0, 'y' => 0, 'width' => 1050, 'height' => 12, 'color' => '#13C878', 'z_index' => 0],
                        ['type' => 'logo', 'field' => 'logo', 'x' => 60, 'y' => 34, 'width' => 130, 'height' => 52, 'z_index' => 2],
                        ['type' => 'photo', 'field' => 'photo', 'x' => 80, 'y' => 360, 'width' => 190, 'height' => 200, 'z_index' => 2],
                        ['type' => 'text', 'field' => 'full_name', 'x' => 330, 'y' => 170, 'width' => 480, 'height' => 44, 'color' => '#f2f6fa', 'font_size' => 32, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'job_title', 'x' => 330, 'y' => 222, 'width' => 460, 'height' => 24, 'color' => '#a9c9e6', 'font_size' => 17, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'company', 'x' => 330, 'y' => 252, 'width' => 460, 'height' => 22, 'color' => '#7d91a5', 'font_size' => 14, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'email', 'x' => 330, 'y' => 360, 'width' => 380, 'height' => 18, 'color' => '#a7b7c8', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'phone', 'x' => 330, 'y' => 386, 'width' => 300, 'height' => 18, 'color' => '#a7b7c8', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'identifier', 'x' => 330, 'y' => 420, 'width' => 320, 'height' => 18, 'color' => '#63748a', 'font_size' => 12, 'z_index' => 3],
                        ['type' => 'qr_code', 'x' => 800, 'y' => 340, 'width' => 180, 'height' => 180, 'z_index' => 4],
                    ],
                ],
            ],
            [
                'slug' => 'business',
                'name' => 'Business',
                'description' => 'Carte claire colonne sombre, portrait photo, coordonnées et QR.',
                'category' => 'business',
                'is_premium' => false,
                'configuration' => [
                    'width' => 1050,
                    'height' => 600,
                    'background' => '#eef1f4',
                    'elements' => [
                        ['type' => 'shape', 'x' => 0, 'y' => 0, 'width' => 310, 'height' => 600, 'color' => '#10281f', 'z_index' => 0],
                        ['type' => 'shape', 'x' => 310, 'y' => 0, 'width' => 740, 'height' => 600, 'color' => '#f6f8fa', 'z_index' => 0],
                        ['type' => 'logo', 'field' => 'logo', 'x' => 70, 'y' => 40, 'width' => 150, 'height' => 56, 'z_index' => 2],
                        ['type' => 'photo', 'field' => 'photo', 'x' => 60, 'y' => 170, 'width' => 190, 'height' => 230, 'z_index' => 2],
                        ['type' => 'text', 'field' => 'full_name', 'x' => 380, 'y' => 180, 'width' => 470, 'height' => 44, 'color' => '#0f2a1f', 'font_size' => 32, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'job_title', 'x' => 380, 'y' => 232, 'width' => 440, 'height' => 24, 'color' => '#3f5c4e', 'font_size' => 17, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'company', 'x' => 380, 'y' => 262, 'width' => 440, 'height' => 22, 'color' => '#5f786c', 'font_size' => 14, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'email', 'x' => 380, 'y' => 360, 'width' => 380, 'height' => 18, 'color' => '#33463e', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'phone', 'x' => 380, 'y' => 386, 'width' => 300, 'height' => 18, 'color' => '#33463e', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'identifier', 'x' => 380, 'y' => 420, 'width' => 320, 'height' => 18, 'color' => '#9aa89f', 'font_size' => 12, 'z_index' => 3],
                        ['type' => 'qr_code', 'x' => 790, 'y' => 350, 'width' => 180, 'height' => 180, 'z_index' => 4],
                    ],
                ],
            ],
            [
                'slug' => 'member',
                'name' => 'Membre',
                'description' => 'Carte émeraude du studio, photo, coordonnées et QR.',
                'category' => 'member',
                'is_premium' => false,
                'configuration' => [
                    'width' => 1050,
                    'height' => 600,
                    'background' => '#0A241A',
                    'elements' => [
                        ['type' => 'shape', 'x' => 0, 'y' => 0, 'width' => 1050, 'height' => 16, 'color' => '#13C878', 'z_index' => 0],
                        ['type' => 'logo', 'field' => 'logo', 'x' => 60, 'y' => 44, 'width' => 140, 'height' => 52, 'z_index' => 2],
                        ['type' => 'photo', 'field' => 'photo', 'x' => 70, 'y' => 180, 'width' => 170, 'height' => 210, 'z_index' => 2],
                        ['type' => 'text', 'field' => 'full_name', 'x' => 290, 'y' => 190, 'width' => 520, 'height' => 44, 'color' => '#effff6', 'font_size' => 32, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'job_title', 'x' => 290, 'y' => 242, 'width' => 460, 'height' => 24, 'color' => '#7fd4ac', 'font_size' => 17, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'company', 'x' => 290, 'y' => 272, 'width' => 460, 'height' => 22, 'color' => '#86b39f', 'font_size' => 14, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'email', 'x' => 290, 'y' => 370, 'width' => 400, 'height' => 18, 'color' => '#a9d6c3', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'phone', 'x' => 290, 'y' => 396, 'width' => 320, 'height' => 18, 'color' => '#a9d6c3', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'identifier', 'x' => 290, 'y' => 432, 'width' => 320, 'height' => 18, 'color' => '#6f9b87', 'font_size' => 12, 'z_index' => 3],
                        ['type' => 'qr_code', 'x' => 790, 'y' => 340, 'width' => 180, 'height' => 180, 'z_index' => 4],
                    ],
                ],
            ],
            [
                'slug' => 'student',
                'name' => 'Étudiant',
                'description' => 'Carte violette épurée, mise en avant du nom et du matricule.',
                'category' => 'student',
                'is_premium' => false,
                'configuration' => [
                    'width' => 1050,
                    'height' => 600,
                    'background' => '#1c1830',
                    'elements' => [
                        ['type' => 'shape', 'x' => 0, 'y' => 540, 'width' => 1050, 'height' => 60, 'color' => '#2c2650', 'z_index' => 0],
                        ['type' => 'shape', 'x' => 60, 'y' => 118, 'width' => 150, 'height' => 8, 'color' => '#7c5cff', 'z_index' => 1],
                        ['type' => 'logo', 'field' => 'logo', 'x' => 60, 'y' => 38, 'width' => 130, 'height' => 50, 'z_index' => 2],
                        ['type' => 'text', 'field' => 'full_name', 'x' => 60, 'y' => 165, 'width' => 700, 'height' => 46, 'color' => '#ffffff', 'font_size' => 34, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'job_title', 'x' => 60, 'y' => 222, 'width' => 560, 'height' => 24, 'color' => '#b9aee6', 'font_size' => 17, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'company', 'x' => 60, 'y' => 256, 'width' => 560, 'height' => 22, 'color' => '#8f86b8', 'font_size' => 14, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'identifier', 'x' => 60, 'y' => 370, 'width' => 420, 'height' => 18, 'color' => '#b9aee6', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'email', 'x' => 60, 'y' => 398, 'width' => 420, 'height' => 18, 'color' => '#9c93c4', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'phone', 'x' => 60, 'y' => 424, 'width' => 360, 'height' => 18, 'color' => '#9c93c4', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'qr_code', 'x' => 800, 'y' => 340, 'width' => 180, 'height' => 180, 'z_index' => 4],
                    ],
                ],
            ],
            [
                'slug' => 'event',
                'name' => 'Événement',
                'description' => 'Modèle premium à bannière photo, style badge événement.',
                'category' => 'event',
                'is_premium' => true,
                'configuration' => [
                    'width' => 1050,
                    'height' => 600,
                    'background' => '#14110e',
                    'elements' => [
                        ['type' => 'photo', 'field' => 'photo', 'x' => 0, 'y' => 0, 'width' => 1050, 'height' => 330, 'z_index' => 0],
                        ['type' => 'shape', 'x' => 0, 'y' => 300, 'width' => 1050, 'height' => 300, 'color' => '#15120e', 'z_index' => 1],
                        ['type' => 'logo', 'field' => 'logo', 'x' => 60, 'y' => 348, 'width' => 130, 'height' => 50, 'z_index' => 2],
                        ['type' => 'text', 'field' => 'full_name', 'x' => 70, 'y' => 420, 'width' => 680, 'height' => 44, 'color' => '#faf3e8', 'font_size' => 32, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'job_title', 'x' => 70, 'y' => 474, 'width' => 560, 'height' => 22, 'color' => '#d8c6a4', 'font_size' => 15, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'email', 'x' => 60, 'y' => 530, 'width' => 460, 'height' => 18, 'color' => '#a08f74', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'qr_code', 'x' => 830, 'y' => 400, 'width' => 160, 'height' => 160, 'z_index' => 4],
                    ],
                ],
            ],
            [
                'slug' => 'cobalt',
                'name' => 'Cobalt',
                'description' => 'Modèle premium bleu cobalt, portrait à droite et QR intégré.',
                'category' => 'premium',
                'is_premium' => true,
                'configuration' => [
                    'width' => 1050,
                    'height' => 600,
                    'background' => '#0a1128',
                    'elements' => [
                        ['type' => 'shape', 'x' => 0, 'y' => 0, 'width' => 1050, 'height' => 10, 'color' => '#5B8CFF', 'z_index' => 0],
                        ['type' => 'logo', 'field' => 'logo', 'x' => 70, 'y' => 46, 'width' => 150, 'height' => 58, 'z_index' => 2],
                        ['type' => 'photo', 'field' => 'photo', 'x' => 760, 'y' => 140, 'width' => 230, 'height' => 300, 'z_index' => 2],
                        ['type' => 'text', 'field' => 'full_name', 'x' => 70, 'y' => 210, 'width' => 600, 'height' => 46, 'color' => '#eaf0ff', 'font_size' => 34, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'job_title', 'x' => 70, 'y' => 264, 'width' => 560, 'height' => 24, 'color' => '#9db1e8', 'font_size' => 17, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'company', 'x' => 70, 'y' => 296, 'width' => 560, 'height' => 22, 'color' => '#7a8fc4', 'font_size' => 14, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'email', 'x' => 70, 'y' => 400, 'width' => 420, 'height' => 18, 'color' => '#aabcf0', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'phone', 'x' => 70, 'y' => 426, 'width' => 340, 'height' => 18, 'color' => '#aabcf0', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'qr_code', 'x' => 70, 'y' => 470, 'width' => 120, 'height' => 120, 'z_index' => 4],
                    ],
                ],
            ],
            [
                'slug' => 'minimal',
                'name' => 'Minimal',
                'description' => 'Modèle premium épuré sur fond clair, lisible immédiatement.',
                'category' => 'premium',
                'is_premium' => true,
                'configuration' => [
                    'width' => 1050,
                    'height' => 600,
                    'background' => '#f7f7f5',
                    'elements' => [
                        ['type' => 'logo', 'field' => 'logo', 'x' => 70, 'y' => 52, 'width' => 140, 'height' => 54, 'z_index' => 2],
                        ['type' => 'text', 'field' => 'full_name', 'x' => 70, 'y' => 210, 'width' => 640, 'height' => 40, 'color' => '#141414', 'font_size' => 28, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'job_title', 'x' => 70, 'y' => 256, 'width' => 520, 'height' => 22, 'color' => '#666666', 'font_size' => 15, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'email', 'x' => 70, 'y' => 360, 'width' => 400, 'height' => 18, 'color' => '#3a3a3a', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'text', 'field' => 'phone', 'x' => 70, 'y' => 386, 'width' => 340, 'height' => 18, 'color' => '#3a3a3a', 'font_size' => 13, 'z_index' => 3],
                        ['type' => 'qr_code', 'x' => 790, 'y' => 340, 'width' => 180, 'height' => 180, 'z_index' => 4],
                    ],
                ],
            ],
        ];
    }
}
