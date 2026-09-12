<?php

/**
 * Configuración propia de Clínica UroCenter.
 *
 * Recursos de marca: los archivos finales viven en `public/assets/img/brand/`
 * y se generan con `php scripts/generar-marca.php` a partir de los originales
 * que están en `public/assets/img/brand/origen-cliente/`.
 *
 * Para cambiar el logo basta reemplazar los archivos de `brand/` (mismos
 * nombres) o volver a ejecutar el script con otro origen.
 */
return [

    'marca' => [
        /** Logo con texto, fondo claro — topbar, sidebar expandido y login */
        'horizontal' => 'assets/img/brand/logo-horizontal.png',

        /** Logo vertical (icono sobre texto) — acceso, documentos e impresión */
        'vertical'   => 'assets/img/brand/logo-vertical.png',

        /** Solo el símbolo (PNG con transparencia) — sidebar colapsado */
        'icono'      => 'assets/img/brand/logo-icono.png',

        /** Iconos de navegador */
        'favicon'    => 'assets/img/brand/favicon.png',
        'apple'      => 'assets/img/brand/apple-icon.png',
    ],

    /**
     * Parámetros de negocio por defecto (se administrarán desde la tabla `parametros`).
     */
    'parametros' => [
        'paginacion'  => 10,
        'moneda'      => 'S/',
        'igv'         => 18.00,
        'zona_horaria' => 'America/Lima',
    ],
];
