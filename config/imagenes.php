<?php

/**
 * Imágenes por tipo de inmueble: lee public/data/imagenes.json (fuente única).
 * Si el inmueble trae foto subida en public/uploads, esa tiene prioridad.
 */

/** URLs de Unsplash para un tipo: apartamento | casa | local | bodega. */
function imagenesPorTipo(string $tipo): array
{
    static $cache = null;

    if ($cache === null) {
        $json = file_get_contents(__DIR__ . '/../public/data/imagenes.json');
        $cache = $json ? (json_decode($json, true) ?: []) : [];
    }

    $urls = array_values(array_filter(array_map(
        fn($item) => ($item['tipo'] ?? '') === $tipo ? ($item['imagen'] ?? null) : null,
        $cache
    )));

    return $urls ?: ['https://placehold.co/800x500?text=' . urlencode($tipo)];
}

/** Foto de una tarjeta: subida propia o la de su tipo según su índice. */
function imagenInmueble(array $inmueble, int $indice = 0): string
{
    if (!empty($inmueble['imagen'])) {
        return 'public/uploads/' . $inmueble['imagen'];
    }

    $urls = imagenesPorTipo((string) ($inmueble['tipo'] ?? 'apartamento'));
    $indice = ((int) ($inmueble['id'] ?? $indice)) % count($urls);

    return $urls[$indice];
}
