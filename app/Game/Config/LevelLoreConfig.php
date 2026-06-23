<?php

namespace App\Game\Config;

class LevelLoreConfig
{
    /**
     * @return array<int, array<int, array<string, mixed>>>
     */
    public static function all(): array
    {
        return [
            1 => [
                [
                    'title' => 'Leon',
                    'subtitle' => 'Dossie do sobrevivente',
                    'files' => ['personagem/wiki_personagem.txt'],
                    'images' => [
                        ['path' => 'personagem/person_view-front.png', 'label' => 'Frente'],
                        ['path' => 'personagem/person_view-front-left.png', 'label' => 'Frente esquerda'],
                        ['path' => 'personagem/person_view-front-right.png', 'label' => 'Frente direita'],
                        ['path' => 'personagem/person_view-back.png', 'label' => 'Costas'],
                        ['path' => 'personagem/walk_down_1.png', 'label' => 'Movimento'],
                        ['path' => 'personagem/walk_up_1.png', 'label' => 'Recuo'],
                    ],
                ],
            ],
            3 => [
                [
                    'title' => 'Don Jose',
                    'subtitle' => 'Primeiro infectado identificado',
                    'files' => ['zombie/Don Jose/Don Jose.txt'],
                    'images' => [
                        ['path' => 'zombie/Don Jose/left.png', 'label' => 'Perfil esquerdo'],
                        ['path' => 'zombie/Don Jose/right.png', 'label' => 'Perfil direito'],
                        ['path' => 'zombie/Don Jose/back.png', 'label' => 'Costas'],
                        ['path' => 'zombie/Don Jose/walk-left.png', 'label' => 'Movimento esquerdo'],
                        ['path' => 'zombie/Don Jose/walk-right.png', 'label' => 'Movimento direito'],
                    ],
                ],
            ],
            4 => [
                [
                    'title' => 'Garrador',
                    'subtitle' => 'Ameaca cega de alta letalidade',
                    'files' => [
                        'zombie/Garrador.txt',
                        'zombie/Garrador/Garrador.txt',
                    ],
                    'images' => [
                        ['path' => 'zombie/Garrador/garrador_idle.png', 'label' => 'Idle'],
                        ['path' => 'zombie/Garrador/garrador_stop.png', 'label' => 'Vigia'],
                        ['path' => 'zombie/Garrador/garrador_stop_right.png', 'label' => 'Vigia direita'],
                        ['path' => 'zombie/Garrador/garrador_walk.png', 'label' => 'Patrulha'],
                        ['path' => 'zombie/Garrador/garrador_walk_right.png', 'label' => 'Patrulha direita'],
                        ['path' => 'zombie/Garrador/garrador_attack.png', 'label' => 'Ataque'],
                    ],
                ],
            ],
            5 => [
                [
                    'title' => 'Zealot',
                    'subtitle' => 'Cultista de Los Illuminados',
                    'files' => ['zombie/zealots/zealot.txt'],
                    'images' => [
                        ['path' => 'zombie/zealots/zealot_idle.png', 'label' => 'Idle'],
                        ['path' => 'zombie/zealots/zealot_walk_1.png', 'label' => 'Movimento'],
                        ['path' => 'zombie/zealots/zealot_shield_broken.png', 'label' => 'Escudo quebrado'],
                    ],
                ],
                [
                    'title' => 'Ganado',
                    'subtitle' => 'Aldeao infectado por Las Plagas',
                    'files' => ['zombie/Ganados/Ganados.txt'],
                    'images' => [
                        ['path' => 'zombie/Ganados/front.png', 'label' => 'Frente'],
                        ['path' => 'zombie/Ganados/left.png', 'label' => 'Perfil esquerdo'],
                        ['path' => 'zombie/Ganados/right.png', 'label' => 'Perfil direito'],
                        ['path' => 'zombie/Ganados/back.png', 'label' => 'Costas'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function forLevel(int $level): array
    {
        return self::all()[$level] ?? [];
    }
}
