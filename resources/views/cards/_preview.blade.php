@php
    $width = (int) ($config['width'] ?? 1050);
    $height = (int) ($config['height'] ?? 600);
    $elements = $config['elements'] ?? [];
    $fields = array_column($elements, 'field');

    if (! empty($data['photo']) && ! in_array('photo', $fields, true)) {
        $elements[] = ['type' => 'image', 'field' => 'photo', 'x' => 820, 'y' => 80, 'width' => 150, 'height' => 180, 'z_index' => 2];
    }
    if (! empty($data['logo']) && ! in_array('logo', $fields, true)) {
        $elements[] = ['type' => 'logo', 'field' => 'logo', 'x' => 70, 'y' => 35, 'width' => 130, 'height' => 55, 'z_index' => 2];
    }
    usort($elements, fn ($a, $b) => ($a['z_index'] ?? 0) <=> ($b['z_index'] ?? 0));

    $pct = fn (int|float $value, int|float $total) => round($value / max(1, $total) * 100, 4);
@endphp

<div class="relative w-full overflow-hidden" style="container-type: inline-size; aspect-ratio: {{ $width }}/{{ $height }}; background: {{ $config['background'] ?? '#ffffff' }};">
    @foreach ($elements as $element)
        @php
            $style = sprintf(
                'left:%s%%;top:%s%%;width:%s%%;height:%s%%;z-index:%d;',
                $pct($element['x'] ?? 0, $width),
                $pct($element['y'] ?? 0, $height),
                $pct($element['width'] ?? $width, $width),
                $pct($element['height'] ?? 40, $height),
                $element['z_index'] ?? 0
            );
            $type = strtolower((string) ($element['type'] ?? ''));
            $value = (string) ($data[$element['field'] ?? ''] ?? $element['content'] ?? '');
        @endphp
        @if($type === 'shape' || $type === 'background')
            <div class="absolute" style="{{ $style }}background: {{ $element['color'] ?? '#eeeeee' }};"></div>
        @elseif($type === 'text')
            <span class="absolute block truncate font-semibold" style="{{ $style }}font-size: {{ round(($element['font_size'] ?? 22) / max(1, $width) * 100, 3) }}cqw; color: {{ $element['color'] ?? '#111827' }}; line-height:1.15;">{{ $value !== '' ? $value : ($element['field'] ?? 'Texte') }}</span>
        @elseif($type === 'qr_code')
            <div class="absolute grid place-items-center rounded-[4%]" style="{{ $style }}background:#fff;">
                <span class="text-center font-bold text-[#0a241a]" style="font-size: 1.6cqw; line-height:1.2;">QR<br>CODE</span>
            </div>
        @elseif($type === 'image' || $type === 'logo')
            @php($asset = $card && ! empty($data[$element['field'] ?? '']) ? route('cards.asset', [$card, $element['field']]) : null)
            @if($asset)
                <img class="absolute object-contain" style="{{ $style }}" src="{{ $asset }}" alt="{{ $element['field'] ?? '' }}">
            @elseif($card)
                <div class="absolute grid place-items-center border border-dashed" style="{{ $style }}border-color:rgba(0,0,0,.25);background:rgba(0,0,0,.06);font-size:1.4cqw;color:rgba(0,0,0,.4);">{{ $element['field'] === 'logo' ? 'Logo' : 'Photo' }}</div>
            @endif
        @endif
    @endforeach

    @if(! $elements)
        <div class="grid h-full place-items-center text-black/30" style="font-size:1.6cqw;">Aucun aperçu</div>
    @endif
</div>
