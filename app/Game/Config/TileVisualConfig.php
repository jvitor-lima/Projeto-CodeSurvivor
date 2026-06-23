<?php

namespace App\Game\Config;

class TileVisualConfig
{
    public static function assetFor(
        string $type,
        array $map = [],
        int $x = 0,
        int $y = 0,
        string $atmosphere = 'default'
    ): string {
        if ($type === 'caminho') {
            return 'mundo/tiles/transparent.svg';
        }

        if ($type === 'grade' || $type === 'obstaculo') {
            return 'mundo/tiles/transparent.svg';
        }

        return 'mundo/tiles/transparent.svg';
    }

    public static function sceneryAssetFor(string $type, string $atmosphere = 'default'): string
    {
        return 'mundo/tiles/transparent.svg';
    }

    public static function objectiveAsset(): string
    {
        return 'mundo/tiles/transparent.svg';
    }

    public static function tileClasses(string $type, string $atmosphere = 'default'): string
    {
        $baseClass = match ($type) {
            'caminho' => 'tile-floor',
            'grade', 'obstaculo' => 'tile-obstacle',
            default => 'tile-scenery',
        };

        if ($type === 'caminho') {
            // Apenas a atmosfera 'laboratory' (Fase 2) usa o estilo sólido.
            // Todas as outras, incluindo 'radioactive' (Fase 3), usam o estilo sutil.
            $envClass = ($atmosphere === 'laboratory') ? 'path-lab' : 'path-street';
            return "{$baseClass} {$envClass}";
        }

        return $baseClass;
    }
}
