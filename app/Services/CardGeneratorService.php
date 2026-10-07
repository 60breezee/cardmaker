<?php

namespace App\Services;

use App\Models\Card;
use App\Models\CardExport;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CardGeneratorService
{
    public function __construct(private readonly TemplateEngine $engine) {}

    public function generate(Card $card, string $format = 'png'): CardExport
    {
        $format = strtolower($format);
        if (! in_array($format, ['png', 'jpg', 'jpeg'], true)) {
            throw new RuntimeException('Format non supporté.');
        }

        $contents = $this->renderImage($card, $format);
        $path = 'cards/'.$card->id.'/'.Str::uuid().'.'.$format;
        Storage::disk()->put($path, $contents);
        $card->update(['status' => 'generated']);

        return $card->exports()->create([
            'user_id' => $card->user_id,
            'format' => strtoupper($format === 'jpeg' ? 'jpg' : $format),
            'file_path' => $path,
            'file_size' => strlen($contents),
        ]);
    }

    public function renderImage(Card $card, string $format = 'png'): string
    {
        $format = strtolower($format);
        if (! in_array($format, ['png', 'jpg', 'jpeg'], true)) {
            throw new RuntimeException('Format non supporté.');
        }

        $scene = $this->engine->render($card->template, $card->data);
        $canvas = imagecreatetruecolor($scene['width'], $scene['height']);
        imagealphablending($canvas, true);
        imagefill($canvas, 0, 0, $this->color($scene['background']));
        $elements = $scene['elements'];
        if (! empty($scene['data']['photo']) && ! in_array('photo', array_column($elements, 'field'), true)) {
            $elements[] = ['type' => 'image', 'field' => 'photo', 'x' => 820, 'y' => 80, 'width' => 150, 'height' => 180, 'z_index' => 2];
        }
        if (! empty($scene['data']['logo']) && ! in_array('logo', array_column($elements, 'field'), true)) {
            $elements[] = ['type' => 'logo', 'field' => 'logo', 'x' => 70, 'y' => 35, 'width' => 130, 'height' => 55, 'z_index' => 2];
        }
        usort($elements, fn (array $a, array $b) => ($a['z_index'] ?? 0) <=> ($b['z_index'] ?? 0));
        foreach ($elements as $element) {
            $this->drawElement($canvas, $element, $scene['data'], $card);
        }

        ob_start();
        $format === 'png' ? imagepng($canvas, null, 6) : imagejpeg($canvas, null, 92);
        $contents = ob_get_clean();
        imagedestroy($canvas);

        if ($contents === false) {
            throw new RuntimeException('Impossible de générer l’image.');
        }

        return $contents;
    }

    private function drawElement($canvas, array $element, array $data, Card $card): void
    {
        $x = (int) ($element['x'] ?? 0);
        $y = (int) ($element['y'] ?? 0);
        $w = (int) ($element['width'] ?? 0);
        $h = (int) ($element['height'] ?? 0);
        $type = strtolower((string) ($element['type'] ?? ''));
        $value = (string) ($data[$element['field'] ?? ''] ?? $element['content'] ?? '');
        if ($type === 'background' || $type === 'shape') {
            imagefilledrectangle($canvas, $x, $y, $x + $w, $y + $h, $this->color($element['color'] ?? '#eeeeee'));

            return;
        }
        if ($type === 'text') {
            imagestring($canvas, 5, $x, $y, $value, $this->color($element['color'] ?? '#111827'));

            return;
        }
        if ($type === 'image' || $type === 'logo') {
            $file = $data[$element['field'] ?? ''] ?? null;
            if ($file && Storage::disk()->exists($file)) {
                $source = @imagecreatefromstring(Storage::disk()->get($file));
                if ($source) {
                    imagecopyresampled($canvas, $source, $x, $y, 0, 0, $w ?: imagesx($source), $h ?: imagesy($source), imagesx($source), imagesy($source));
                    imagedestroy($source);
                }
            }

            return;
        }
        if ($type === 'qr_code') {
            $payload = url('/verify/'.$card->public_identifier);
            $options = new QROptions(['outputType' => QRGdImagePNG::class, 'scale' => max(2, (int) (($w ?: 180) / 45))]);
            $source = @imagecreatefromstring((new QRCode($options))->render($payload));
            if ($source) {
                imagecopy($canvas, $source, $x, $y, 0, 0, imagesx($source), imagesy($source));
                imagedestroy($source);
            }
        }
    }

    private function color(string $hex): int
    {
        $hex = ltrim($hex, '#');
        $hex = strlen($hex) === 3 ? preg_replace('/(.)/', '$1$1', $hex) : $hex;

        return (hexdec(substr($hex, 0, 2)) << 16) | (hexdec(substr($hex, 2, 2)) << 8) | hexdec(substr($hex, 4, 2));
    }
}
