{{-- resources/views/components/clock-btn.blade.php --}}
@props([
    /* クリック時に送る値。省略可にしたいので '' をデフォルトに  */
    'type'  => '',

    /* 表示文字列（スロット or 属性）                            */
    'label' => null,
])

@php
    // clock_in → btn-clock-in のように変換（空なら '' のまま）
    $btnClass = $type ? 'btn-' . str_replace('_','-',$type) : '';
@endphp

<button type="button"
        {{ $attributes->merge(['class' => "btn-clock {$btnClass}"]) }}
        data-clock-type="{{ $type }}">
    {{ $label ?? $slot }}
</button>
