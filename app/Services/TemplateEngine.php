<?php

namespace App\Services;

use App\Models\Template;
use InvalidArgumentException;

class TemplateEngine
{
    public function render(Template $template, array $data): array
    {
        $configuration = $template->configuration;
        $width = (int) ($configuration['width'] ?? 1050);
        $height = (int) ($configuration['height'] ?? 600);
        if ($width < 100 || $height < 100 || $width > 5000 || $height > 5000) {
            throw new InvalidArgumentException('Configuration de template invalide.');
        }

        return ['width' => $width, 'height' => $height, 'background' => $configuration['background'] ?? '#ffffff', 'elements' => $configuration['elements'] ?? [], 'data' => $data];
    }
}
