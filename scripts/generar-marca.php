<?php

/**
 * Genera los recursos de marca de Clínica UroCenter a partir de los originales
 * entregados por el cliente.
 *
 * Uso:
 *   php scripts/generar-marca.php
 *
 * Entrada : public/assets/img/brand/origen-cliente/
 * Salida  : public/assets/img/brand/  (archivos que consume la aplicación)
 *
 * Los JPG/PNG entregados traen mucho margen y fondo claro: este script recorta
 * el fondo, escala y aplana sobre blanco para que el logo se vea nítido en el
 * topbar, el sidebar y la pantalla de acceso.
 *
 * Requiere la extensión GD (incluida en Laragon).
 */

$base   = dirname(__DIR__) . '/public/assets/img/brand';
$origen = $base . '/origen-cliente';
$salida = $base;

if (! extension_loaded('gd')) {
    fwrite(STDERR, "La extensión GD no está habilitada.\n");
    exit(1);
}

/* -------------------------------------------------------------------------
 | Utilidades de imagen
 * ------------------------------------------------------------------------- */

function cargar(string $ruta): GdImage
{
    $info = getimagesize($ruta);

    $imagen = match ($info[2] ?? null) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($ruta),
        IMAGETYPE_PNG  => imagecreatefrompng($ruta),
        IMAGETYPE_WEBP => imagecreatefromwebp($ruta),
        IMAGETYPE_GIF  => imagecreatefromgif($ruta),
        default        => false,
    };

    if (! $imagen) {
        fwrite(STDERR, "No se pudo leer la imagen: {$ruta}\n");
        exit(1);
    }

    return $imagen;
}

/**
 * Recorta el margen claro (fondo) alrededor del contenido.
 */
function recortarFondo(GdImage $imagen, int $umbralBrillo = 236, int $umbralSaturacion = 16): GdImage
{
    $ancho = imagesx($imagen);
    $alto  = imagesy($imagen);

    $minX = $ancho;
    $minY = $alto;
    $maxX = 0;
    $maxY = 0;

    // Se muestrea cada 2 px: suficiente precisión y el doble de rápido
    for ($y = 0; $y < $alto; $y += 2) {
        for ($x = 0; $x < $ancho; $x += 2) {
            $rgb = imagecolorat($imagen, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            $brillo = ($r + $g + $b) / 3;
            $saturacion = max($r, $g, $b) - min($r, $g, $b);

            // ¿Es fondo? (claro y sin color)
            if ($brillo > $umbralBrillo && $saturacion < $umbralSaturacion) {
                continue;
            }

            if ($x < $minX) $minX = $x;
            if ($x > $maxX) $maxX = $x;
            if ($y < $minY) $minY = $y;
            if ($y > $maxY) $maxY = $y;
        }
    }

    if ($maxX <= $minX || $maxY <= $minY) {
        return $imagen; // no se detectó contenido: se deja igual
    }

    $anchoContenido = $maxX - $minX;
    $altoContenido  = $maxY - $minY;

    // Margen del 2 % para que el logo no quede pegado al borde
    $margenX = (int) round($anchoContenido * 0.02);
    $margenY = (int) round($altoContenido * 0.02);

    $x = max(0, $minX - $margenX);
    $y = max(0, $minY - $margenY);
    $w = min($ancho - $x, $anchoContenido + ($margenX * 2));
    $h = min($alto - $y, $altoContenido + ($margenY * 2));

    $recorte = imagecrop($imagen, ['x' => $x, 'y' => $y, 'width' => $w, 'height' => $h]);

    return $recorte ?: $imagen;
}

/**
 * Escala la imagen si supera el ancho máximo indicado.
 */
function escalarPorAncho(GdImage $imagen, int $anchoMaximo): GdImage
{
    $ancho = imagesx($imagen);
    $alto  = imagesy($imagen);

    if ($ancho <= $anchoMaximo) {
        return $imagen;
    }

    $nuevoAlto = (int) round($alto * ($anchoMaximo / $ancho));
    $escalada  = imagescale($imagen, $anchoMaximo, $nuevoAlto, IMG_BICUBIC);

    return $escalada ?: $imagen;
}

/**
 * Aplana la imagen sobre un color (los JPG no admiten transparencia y las
 * imágenes con fondo claro se ven mejor sobre blanco).
 */
function aplanar(GdImage $imagen, array $color = [255, 255, 255]): GdImage
{
    $ancho = imagesx($imagen);
    $alto  = imagesy($imagen);

    $lienzo = imagecreatetruecolor($ancho, $alto);
    imagefilledrectangle(
        $lienzo,
        0, 0, $ancho, $alto,
        imagecolorallocate($lienzo, $color[0], $color[1], $color[2])
    );
    imagealphablending($lienzo, true);
    imagecopy($lienzo, $imagen, 0, 0, 0, 0, $ancho, $alto);

    return $lienzo;
}

/**
 * Centra la imagen dentro de un lienzo cuadrado (modo "contain").
 */
function contenerEnCuadrado(GdImage $imagen, int $lado, bool $fondoTransparente = false): GdImage
{
    $ancho = imagesx($imagen);
    $alto  = imagesy($imagen);
    $factor = min($lado / $ancho, $lado / $alto);
    $nuevoAncho = max(1, (int) round($ancho * $factor));
    $nuevoAlto  = max(1, (int) round($alto * $factor));

    $lienzo = imagecreatetruecolor($lado, $lado);

    if ($fondoTransparente) {
        imagealphablending($lienzo, false);
        imagesavealpha($lienzo, true);
        imagefill($lienzo, 0, 0, imagecolorallocatealpha($lienzo, 255, 255, 255, 127));
        imagealphablending($lienzo, true);
    } else {
        imagefilledrectangle($lienzo, 0, 0, $lado, $lado, imagecolorallocate($lienzo, 255, 255, 255));
    }

    imagecopyresampled(
        $lienzo, $imagen,
        (int) round(($lado - $nuevoAncho) / 2), (int) round(($lado - $nuevoAlto) / 2),
        0, 0,
        $nuevoAncho, $nuevoAlto,
        $ancho, $alto
    );

    return $lienzo;
}

/* -------------------------------------------------------------------------
 | Definición de las variantes
 * ------------------------------------------------------------------------- */

$variantes = [
    // Logo con texto (topbar, sidebar expandido, login).
    // Umbral alto (210) porque el JPG trae formas grises muy claras de fondo
    // que, con el umbral por defecto, se confundirían con contenido.
    ['origen' => 'LOGO2.jpg',  'destino' => 'logo-horizontal.png', 'ancho' => 900, 'aplanar' => true,  'umbral' => 210],
    // Logo vertical (pantalla de acceso, documentos, impresión)
    ['origen' => 'LOGO3.jpeg', 'destino' => 'logo-vertical.png',   'ancho' => 420, 'aplanar' => true,  'umbral' => 210],
    // Solo el símbolo (sidebar colapsado, app, favicons). Conserva su transparencia.
    ['origen' => 'logo.png',   'destino' => 'logo-icono.png',      'ancho' => 512, 'aplanar' => false, 'umbral' => 236],
];

echo "Generando recursos de marca en public/assets/img/brand\n\n";

foreach ($variantes as $variante) {
    $rutaOrigen = $origen . '/' . $variante['origen'];

    if (! file_exists($rutaOrigen)) {
        fwrite(STDERR, "AVISO: no se encontró {$rutaOrigen}\n");
        continue;
    }

    $imagen = cargar($rutaOrigen);
    $imagen = recortarFondo($imagen, $variante['umbral'] ?? 236);
    $imagen = escalarPorAncho($imagen, $variante['ancho']);

    if ($variante['aplanar']) {
        $imagen = aplanar($imagen);
    }

    imagepng($imagen, $salida . '/' . $variante['destino'], 9);

    printf(
        "  %-22s %4d x %-4d  (%s)\n",
        $variante['destino'],
        imagesx($imagen),
        imagesy($imagen),
        $variante['origen']
    );
}

// Favicon e icono de aplicación a partir del símbolo
$rutaIcono = $salida . '/logo-icono.png';

if (file_exists($rutaIcono)) {
    $icono = cargar($rutaIcono);

    foreach ([['favicon.png', 64], ['apple-icon.png', 180]] as [$nombre, $lado]) {
        $cuadrado = contenerEnCuadrado($icono, $lado);
        imagepng($cuadrado, $salida . '/' . $nombre, 9);
        printf("  %-22s %4d x %-4d  (logo-icono.png)\n", $nombre, $lado, $lado);
    }
}

echo "\nListo. Recursos generados en public/assets/img/brand/\n";
