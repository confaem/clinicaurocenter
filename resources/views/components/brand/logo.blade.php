{{--
    Componente de marca: única fuente de verdad del logo en el sistema.
    Uso:
        <x-brand.logo variante="horizontal" clase="brand-logo" />
        <x-brand.logo variante="icono" clase="brand-logo" />
        <x-brand.logo variante="vertical" clase="brand-logo brand-logo-lg" />

    Las rutas se configuran en config/urocenter.php (clave `marca`).
    El logo entregado es un JPG con fondo claro: se presenta sobre una
    "píldora" blanca (.brand-chip) para que se vea correcto también en el
    tema oscuro y en el sidebar oscuro.
--}}
@props([
    'variante' => 'horizontal',
    'clase'    => 'brand-logo',
    'conChip'  => true,
])

@php
    $ruta = config('urocenter.marca.' . $variante, config('urocenter.marca.horizontal'));
@endphp

@if ($conChip)
    <span class="brand-chip">
        <img src="{{ asset($ruta) }}" alt="{{ config('app.name') }}" class="{{ $clase }}">
    </span>
@else
    <img src="{{ asset($ruta) }}" alt="{{ config('app.name') }}" class="{{ $clase }}">
@endif
