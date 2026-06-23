<?php

namespace App\Game\Config;

class LevelVisualConfig
{
    private const FALLBACK_BACKGROUND = 'mundo/backgrounds/placeholder_background.svg';

    private const LEVEL_VISUALS = [
        1 => [
            'background' => 'mundo/backgrounds/level_1_hospital_abandonado.png',
            'pathStyle' => 'hospital',
            'pathOpacity' => 0.34,
            'gridOpacity' => 0.035,
        ],
        2 => [
            'background' => 'mundo/backgrounds/New_level_2_Deposito_Sobrevivência.png',
            'pathStyle' => 'street',
            'pathOpacity' => 0.34,
            'gridOpacity' => 0.04,
        ],
        3 => [
            'background' => 'mundo/backgrounds/level_2_rua_contaminada.png',
            'pathStyle' => 'street',
            'pathOpacity' => 0.32,
            'gridOpacity' => 0.035,
        ],
        4 => [
            'background' => 'mundo/backgrounds/level_3_laboratorio_subterraneo.png',
            'pathStyle' => 'lab',
            'pathOpacity' => 0.3,
            'gridOpacity' => 0.03,
        ],
        5 => [
            'background' => 'mundo/backgrounds/level_4_corredor_interceptacao.png',
            'pathStyle' => 'street',
            'pathOpacity' => 0.38,
            'gridOpacity' => 0.055,
        ],
        6 => [
            'background' => 'mundo/backgrounds/fase6.png',
            'pathStyle' => 'street',
            'pathOpacity' => 0.38,
            'gridOpacity' => 0.055,
        ],
        7 => [
            'background' => 'mundo/backgrounds/level7_fase.png',
            'pathStyle' => 'street',
            'pathOpacity' => 0.34,
            'gridOpacity' => 0.04,
        ],
    ];

    public static function forLevel(int $level, int $gridSize): array
    {
        $visual = self::LEVEL_VISUALS[$level] ?? self::LEVEL_VISUALS[1];

        return [
            'background' => self::existingBackground($visual['background']),
            'configuredBackground' => $visual['background'],
            'fallbackBackground' => self::FALLBACK_BACKGROUND,
            'isPlaceholder' => ! file_exists(public_path($visual['background'])),
            'pathStyle' => $visual['pathStyle'],
            'pathOpacity' => $visual['pathOpacity'],
            'gridOpacity' => $visual['gridOpacity'],
            'gridSize' => $gridSize,
        ];
    }

    private static function existingBackground(string $background): string
    {
        if (file_exists(public_path($background))) {
            return $background;
        }

        return self::FALLBACK_BACKGROUND;
    }
}
