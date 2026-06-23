<?php

namespace App\Game\Config;

use InvalidArgumentException;

class LevelConfig
{
    public static function getLevelBySequence(int $level): array
    {
        return match ($level) {
            1 => self::map1Phase1(),
            2 => self::map1Phase2(),
            3 => self::map1Phase3(),
            4 => self::map1Phase4(),
            5 => self::map1Phase5(),
            6 => self::map1Phase6(),
            7 => self::map2Phase1(),
            default => throw new InvalidArgumentException("Fase {$level} nao encontrada."),
        };
    }

    public static function getAllLevels(): array
    {
        return [
            1 => ['name' => 'Primeira Rota', 'difficulty' => 'Iniciante', 'mapId' => 1, 'mapLevel' => 1],
            2 => ['name' => 'Coleta de Suprimentos', 'difficulty' => 'Iniciante', 'mapId' => 1, 'mapLevel' => 2],
            3 => ['name' => 'Primeiro Infectado', 'difficulty' => 'Intermediaria', 'mapId' => 1, 'mapLevel' => 3],
            4 => ['name' => 'Fuga do Garrador', 'difficulty' => 'Desafio', 'mapId' => 1, 'mapLevel' => 4],
            5 => ['name' => 'Corredor dos Cultistas', 'difficulty' => 'Intermediaria', 'mapId' => 1, 'mapLevel' => 5],
            6 => ['name' => 'Rota com Repeticao', 'difficulty' => 'Iniciante', 'mapId' => 1, 'mapLevel' => 6],
            7 => ['name' => 'Arsenal da Quarentena', 'difficulty' => 'Iniciante', 'mapId' => 2, 'mapLevel' => 1],
        ];
    }

    public static function getCampaignMaps(): array
    {
        return [
            1 => [
                'name' => 'Cidade em Colapso',
                'subtitle' => 'Movimento e logica',
                'background' => 'mapas/new_campaign_map.png',
                'firstLevel' => 1,
            ],
            2 => [
                'name' => 'Zona de Quarentena',
                'subtitle' => 'Itens e combate',
                'background' => 'mapas/backgrounds/map2_quarantine_zone.png',
                'firstLevel' => 7,
            ],
        ];
    }

    public static function getLevelsForMap(int $mapId): array
    {
        return array_filter(
            self::getAllLevels(),
            fn (array $level) => ($level['mapId'] ?? 1) === $mapId
        );
    }

    public static function getMapForLevel(int $level): int
    {
        return (int) (self::getAllLevels()[$level]['mapId'] ?? 1);
    }

    public static function getMapLevelNumber(int $level): int
    {
        return (int) (self::getAllLevels()[$level]['mapLevel'] ?? $level);
    }

    public static function getMapById(int $mapId): array
    {
        return self::getCampaignMaps()[$mapId] ?? self::getCampaignMaps()[1];
    }

    public static function getMapBackground(int $mapId): string
    {
        return self::getMapById($mapId)['background'] ?? self::getCampaignMaps()[1]['background'];
    }

    public static function getMapIconAsset(string $icon): string
    {
        return match ($icon) {
            'hospital' => 'mapas/icones/hospital_v3.png',
            'delegacia' => 'mapas/icones/delegacia_v2.png',
            'laboratorio' => 'mapas/icones/laboratorio_v2.png',
            'mapa2_armory' => 'mapas/icones/mapa2_armory_icon.png',
            'mapa2_lab' => 'mapas/icones/mapa2_lab_icon.png',
            'mapa2_exit' => 'mapas/icones/mapa2_exit_icon.png',
            default => 'mapas/icones/hospital_v2.png',
        };
    }

    public static function getFirstLevelForMap(int $mapId): int
    {
        return (int) (self::getMapById($mapId)['firstLevel'] ?? 1);
    }

    public static function getFirstIncompleteMap(array $progress): int
    {
        foreach (self::getCampaignMaps() as $mapId => $map) {
            $levels = self::getLevelsForMap((int) $mapId);

            foreach (array_keys($levels) as $level) {
                if (($progress[(string) $level] ?? $progress[$level] ?? 'locked') !== 'completed') {
                    return (int) $mapId;
                }
            }
        }

        return (int) array_key_last(self::getCampaignMaps());
    }

    public static function isMapUnlocked(int $mapId, array $progress): bool
    {
        $firstLevel = self::getFirstLevelForMap($mapId);
        $status = $progress[(string) $firstLevel] ?? $progress[$firstLevel] ?? 'locked';

        return $mapId === 1 || in_array($status, ['available', 'completed'], true);
    }

    public static function mapCompletion(int $mapId, array $progress): array
    {
        $levels = self::getLevelsForMap($mapId);
        $completed = 0;

        foreach (array_keys($levels) as $level) {
            if (($progress[(string) $level] ?? $progress[$level] ?? 'locked') === 'completed') {
                $completed++;
            }
        }

        return ['completed' => $completed, 'total' => count($levels)];
    }
    private static function map1Phase1(): array
    {
        return [
            'name' => 'Primeira Rota',
            'difficulty' => 'Iniciante',
            'objective' => 'Use comandos de movimento para guiar Leon ate o ponto de saida.',
            'gridSize' => 8,
            'phaseType' => 'objetivo',
            'map' => [
                ['parede', 'parede', 'caminho', 'parede', 'parede', 'parede', 'parede', 'parede'],
                ['parede', 'grama', 'caminho', 'grama', 'grama', 'grama', 'grama', 'parede'],
                ['parede', 'grama', 'caminho', 'caminho', 'caminho', 'grama', 'grama', 'parede'],
                ['parede', 'grama', 'grama', 'grama', 'caminho', 'grama', 'grama', 'parede'],
                ['parede', 'grama', 'grama', 'grama', 'caminho', 'grama', 'grama', 'parede'],
                ['parede', 'grama', 'grama', 'grama', 'caminho', 'grama', 'grama', 'parede'],
                ['parede', 'grama', 'grama', 'grama', 'caminho', 'grama', 'grama', 'parede'],
                ['parede', 'parede', 'parede', 'parede', 'caminho', 'parede', 'parede', 'parede'],
            ],
            'player' => ['x' => 2, 'y' => 0, 'direction' => 'front', 'health' => 3, 'maxHealth' => 3],
            'goal' => ['x' => 4, 'y' => 7],
            'zombies' => [],
            'collectibles' => [],
            'scenery' => [
                ['x' => 0, 'y' => 0, 'type' => 'destrocos'],
                ['x' => 1, 'y' => 0, 'type' => 'destrocos'],
                ['x' => 1, 'y' => 1, 'type' => 'sangue'],
                ['x' => 1, 'y' => 2, 'type' => 'caixas'],
                ['x' => 3, 'y' => 1, 'type' => 'caixas'],
                ['x' => 4, 'y' => 1, 'type' => 'destrocos'],
                ['x' => 5, 'y' => 1, 'type' => 'lixo'],
                ['x' => 3, 'y' => 3, 'type' => 'caixas'],
                ['x' => 5, 'y' => 3, 'type' => 'sangue'],
                ['x' => 6, 'y' => 1, 'type' => 'destrocos'],
                ['x' => 6, 'y' => 4, 'type' => 'destrocos'],
                ['x' => 6, 'y' => 6, 'type' => 'lixo'],
                ['x' => 3, 'y' => 7, 'type' => 'barricada'],
                ['x' => 5, 'y' => 7, 'type' => 'destrocos'],
                ['x' => 1, 'y' => 6, 'type' => 'sangue'],
            ],
            'atmosphere' => 'bairro_abandonado',
            'mapIcon' => 'hospital',
            'mapLocation' => ['x' => 150, 'y' => 450],
        ];
    }

    private static function map1Phase2(): array
    {
        return [
            'name' => 'Coleta de Suprimentos',
            'difficulty' => 'Iniciante',
            'objective' => 'Colete o bastao, volte para a rota principal e alcance a saida.',
            'gridSize' => 8,
            'phaseType' => 'objetivo',
            'initialCode' => <<<'CODE'
hero.moveUp(2)
hero.moveLeft(2)

CODE,
            'map' => [
                ['militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar', 'militar'],
                ['militar', 'obstaculo', 'militar', 'caminho', 'militar', 'obstaculo', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar', 'militar'],
                ['militar', 'caixas', 'militar', 'caminho', 'militar', 'caixas', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar', 'militar'],
                ['militar', 'caminho', 'caminho', 'caminho', 'militar', 'obstaculo', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar', 'militar'],
            ],
            'player' => ['x' => 3, 'y' => 7, 'direction' => 'back', 'health' => 3, 'maxHealth' => 3],
            'goal' => ['x' => 3, 'y' => 0],
            'requiredItems' => ['bastao'],
            'zombies' => [],
            'collectibles' => [
                ['id' => 'bastao_level_2', 'itemId' => 'bastao', 'x' => 1, 'y' => 5],
            ],
            'scenery' => [
                ['x' => 1, 'y' => 1, 'type' => 'caixas'],
                ['x' => 5, 'y' => 1, 'type' => 'caixas'],
                ['x' => 1, 'y' => 3, 'type' => 'lixo'],
                ['x' => 5, 'y' => 3, 'type' => 'destrocos'],
                ['x' => 5, 'y' => 5, 'type' => 'caixas'],
                ['x' => 2, 'y' => 6, 'type' => 'sangue'],
                ['x' => 4, 'y' => 6, 'type' => 'destrocos'],
            ],
            'atmosphere' => 'deposito',
            'mapIcon' => 'delegacia',
            'mapLocation' => ['x' => 330, 'y' => 380],
        ];
    }

    private static function map1Phase3(): array
    {
        return [
            'name' => 'Primeiro Infectado',
            'difficulty' => 'Intermediaria',
            'objective' => 'Use hero.attack() para eliminar o infectado antes de atravessar o bloqueio.',
            'gridSize' => 8,
            'phaseType' => 'combate',
            'map' => [
                ['militar', 'militar', 'caminho', 'militar', 'militar', 'militar', 'militar', 'militar'],
                ['militar', 'obstaculo', 'caminho', 'obstaculo', 'militar', 'militar', 'militar', 'militar'],
                ['militar', 'militar', 'caminho', 'caminho', 'caminho', 'militar', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar'],
            ],
            'player' => ['x' => 2, 'y' => 0, 'direction' => 'front', 'health' => 3, 'maxHealth' => 3],
            'goal' => ['x' => 4, 'y' => 7],
            'zombies' => [
                [
                    'name' => 'Don José',
                    'type' => 'don_jose',
                    'x' => 4, 'y' => 4,
                    'direction' => 'front',
                    'health' => 1, 'maxHealth' => 1, 'damage' => 1,
                ],
            ],
            'collectibles' => [],
            'scenery' => [
                ['x' => 1, 'y' => 0, 'type' => 'barricada'],
                ['x' => 3, 'y' => 0, 'type' => 'barricada'],
                ['x' => 0, 'y' => 1, 'type' => 'poste'],
                ['x' => 1, 'y' => 2, 'type' => 'caixas'],
                ['x' => 5, 'y' => 2, 'type' => 'caixas'],
                ['x' => 0, 'y' => 2, 'type' => 'barricada'],
                ['x' => 7, 'y' => 1, 'type' => 'carro'],
                ['x' => 7, 'y' => 3, 'type' => 'carro_destruido'],
                ['x' => 6, 'y' => 5, 'type' => 'caixas'],
                ['x' => 5, 'y' => 3, 'type' => 'sangue'],
                ['x' => 3, 'y' => 3, 'type' => 'fumaca'],
                ['x' => 5, 'y' => 4, 'type' => 'destrocos'],
                ['x' => 3, 'y' => 6, 'type' => 'barricada'],
                ['x' => 5, 'y' => 6, 'type' => 'barricada'],
                ['x' => 7, 'y' => 7, 'type' => 'poste'],
            ],
            'atmosphere' => 'rua_bloqueada',
            'mapIcon' => 'delegacia',
            'mapLocation' => ['x' => 560, 'y' => 290],
        ];
    }

    private static function map1Phase4(): array
    {
        return [
            'name' => 'Fuga do Garrador',
            'difficulty' => 'Desafio',
            'objective' => 'Planeje a rota pelo laboratorio e evite o campo de visao do Garrador.',
            'gridSize' => 9,
            'phaseType' => 'stealth',
            'map' => [
                ['laboratorio', 'laboratorio', 'laboratorio', 'laboratorio', 'caminho', 'laboratorio', 'laboratorio', 'laboratorio', 'laboratorio'],
                ['laboratorio', 'obstaculo', 'laboratorio', 'laboratorio', 'caminho', 'laboratorio', 'laboratorio', 'obstaculo', 'laboratorio'],
                ['laboratorio', 'laboratorio', 'laboratorio', 'laboratorio', 'caminho', 'laboratorio', 'laboratorio', 'laboratorio', 'laboratorio'],
                ['caminho', 'caminho', 'caminho', 'caminho', 'caminho', 'laboratorio', 'laboratorio', 'laboratorio', 'laboratorio'],
                ['caminho', 'grade', 'grade', 'grade', 'caminho', 'caminho', 'caminho', 'caminho', 'caminho'],
                ['caminho', 'grade', 'grade', 'grade', 'caminho', 'laboratorio', 'laboratorio', 'laboratorio', 'laboratorio'],
                ['caminho', 'caminho', 'caminho', 'caminho', 'caminho', 'laboratorio', 'laboratorio', 'laboratorio', 'laboratorio'],
                ['laboratorio', 'laboratorio', 'laboratorio', 'laboratorio', 'caminho', 'laboratorio', 'laboratorio', 'laboratorio', 'laboratorio'],
                ['laboratorio', 'laboratorio', 'laboratorio', 'laboratorio', 'caminho', 'laboratorio', 'laboratorio', 'laboratorio', 'laboratorio'],
            ],
            'player' => ['x' => 4, 'y' => 0, 'direction' => 'front', 'health' => 3, 'maxHealth' => 3],
            'goal' => ['x' => 4, 'y' => 8],
            'zombies' => [
                [
                    'name' => 'Garrador',
                    'x' => 8, 'y' => 4,
                    'direction' => 'left',
                    'health' => 5, 'maxHealth' => 5, 'damage' => 3,
                    'visionRange' => 4,
                    'type' => 'garrador',
                    'patrolPath' => [
                        ['x' => 4, 'y' => 4],
                        ['x' => 8, 'y' => 4],
                    ],
                ],
            ],
            'collectibles' => [],
            'scenery' => [
                ['x' => 3, 'y' => 0, 'type' => 'radioactive_barrel'],
                ['x' => 5, 'y' => 0, 'type' => 'radioactive_barrel'],
                ['x' => 0, 'y' => 1, 'type' => 'caixas'],
                ['x' => 8, 'y' => 1, 'type' => 'caixas'],
                ['x' => 1, 'y' => 4, 'type' => 'radioactive_barrel'],
                ['x' => 2, 'y' => 4, 'type' => 'radioactive_barrel'],
                ['x' => 1, 'y' => 5, 'type' => 'fumaca'],
                ['x' => 2, 'y' => 5, 'type' => 'sangue'],
                ['x' => 0, 'y' => 2, 'type' => 'destrocos'],
                ['x' => 8, 'y' => 3, 'type' => 'lixo'],
                ['x' => 8, 'y' => 5, 'type' => 'sangue'],
                ['x' => 7, 'y' => 5, 'type' => 'destrocos'],
                ['x' => 3, 'y' => 7, 'type' => 'barricada'],
                ['x' => 5, 'y' => 7, 'type' => 'barricada'],
                ['x' => 5, 'y' => 8, 'type' => 'fumaca'],
            ],
            'atmosphere' => 'radioactive',
            'mapIcon' => 'laboratorio',
            'mapLocation' => ['x' => 850, 'y' => 150],
        ];
    }

    private static function map1Phase5(): array
    {
        return [
            'name' => 'Corredor dos Cultistas',
            'difficulty' => 'Intermediaria',
            'objective' => 'Avance pelo corredor, neutralize o Zealot e depois o Ganado com ataques nomeados.',
            'gridSize' => 8,
            'phaseType' => 'combate_linear',
            'initialCode' => <<<'CODE'
hero.moveRight()
hero.attack("zealot")
hero.attack("zealot")
CODE,
            'map' => [
                ['militar', 'militar', 'militar', 'militar', 'militar', 'militar', 'militar', 'militar'],
                ['militar', 'obstaculo', 'caminho', 'militar', 'militar', 'militar', 'militar', 'militar'],
                ['militar', 'militar', 'caminho', 'militar', 'obstaculo', 'militar', 'militar', 'militar'],
                ['militar', 'obstaculo', 'caminho', 'militar', 'militar', 'militar', 'obstaculo', 'militar'],
                ['caminho', 'caminho', 'caminho', 'caminho', 'caminho', 'caminho', 'caminho', 'caminho'],
                ['militar', 'obstaculo', 'militar', 'militar', 'militar', 'caminho', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'obstaculo', 'militar', 'caminho', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'militar', 'militar', 'militar', 'obstaculo', 'militar'],
            ],
            'player' => ['x' => 0, 'y' => 4, 'direction' => 'front-right', 'health' => 3, 'maxHealth' => 3],
            'goal' => ['x' => 7, 'y' => 4],
            'zombies' => [
                [
                    'name' => 'Zealot',
                    'type' => 'zealot',
                    'x' => 2, 'y' => 3,
                    'direction' => 'front',
                    'health' => 2, 'maxHealth' => 2, 'damage' => 1,
                    'activationRange' => 3,
                    'activationTarget' => ['x' => 2, 'y' => 4],
                ],
                [
                    'name' => 'Ganado',
                    'type' => 'ganado',
                    'x' => 5, 'y' => 6,
                    'direction' => 'back',
                    'health' => 1, 'maxHealth' => 1, 'damage' => 1,
                    'activationRange' => 2,
                    'activationTarget' => ['x' => 5, 'y' => 4],
                ],
            ],
            'collectibles' => [],
            'scenery' => [
                ['x' => 0, 'y' => 0, 'type' => 'destrocos'],
                ['x' => 5, 'y' => 0, 'type' => 'poste'],
                ['x' => 1, 'y' => 1, 'type' => 'caixas'],
                ['x' => 5, 'y' => 1, 'type' => 'lixo'],
                ['x' => 4, 'y' => 2, 'type' => 'radioactive_barrel'],
                ['x' => 6, 'y' => 3, 'type' => 'barricada'],
                ['x' => 0, 'y' => 5, 'type' => 'destrocos'],
                ['x' => 1, 'y' => 5, 'type' => 'caixas'],
                ['x' => 6, 'y' => 5, 'type' => 'destrocos'],
                ['x' => 3, 'y' => 6, 'type' => 'sangue'],
                ['x' => 6, 'y' => 7, 'type' => 'barricada'],
            ],
            'atmosphere' => 'rua_bloqueada',
            'mapIcon' => 'delegacia',
            'mapLocation' => ['x' => 1020, 'y' => 470],
        ];
    }

    private static function map1Phase6(): array
    {
        return [
            'name' => 'Rota com Repeticao',
            'difficulty' => 'Iniciante',
            'objective' => 'Use repeat para atravessar a trilha com menos linhas de codigo.',
            'gridSize' => 10,
            'phaseType' => 'objetivo',
            'initialCode' => <<<'CODE'
repeat(2) {
  hero.moveRight(2)
  hero.moveUp(2)
  hero.moveRight(2)
  hero.moveDown(2)
}
CODE,
            'map' => [
                ['parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede'],
                ['parede',  'parede',  'caminho', 'caminho', 'caminho', 'parede',  'caminho', 'caminho', 'caminho', 'parede'],
                ['parede',  'parede',  'caminho', 'parede',  'caminho', 'parede',  'caminho', 'parede',  'caminho', 'parede'],
                ['caminho', 'caminho', 'caminho', 'parede',  'caminho', 'caminho', 'caminho', 'parede',  'caminho', 'parede'],
                ['parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede'],
                ['parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede'],
                ['parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede'],
                ['parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede'],
                ['parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede'],
                ['parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede',  'parede'],
            ],
            'player' => ['x' => 0, 'y' => 3, 'direction' => 'right', 'health' => 3, 'maxHealth' => 3],
            'goal' => ['x' => 8, 'y' => 3],
            'zombies' => [],
            'collectibles' => [],
            'scenery' => [],
            'atmosphere' => 'rua_bloqueada',
            'mapIcon' => 'delegacia',
            'mapLocation' => ['x' => 850, 'y' => 445],
        ];
    }

    private static function map2Phase1(): array
    {
        return [
            'name' => 'Arsenal da Quarentena',
            'difficulty' => 'Iniciante',
            'objective' => 'Colete a Handgun, volte para a rota principal e alcance a extracao superior.',
            'gridSize' => 8,
            'phaseType' => 'objetivo',
            'initialCode' => <<<'CODE'
hero.moveUp(2)
hero.moveLeft(2)
hero.moveRight(2)
hero.moveUp(5)
CODE,
            'map' => [
                ['militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar', 'militar'],
                ['militar', 'obstaculo', 'militar', 'caminho', 'militar', 'obstaculo', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar', 'militar'],
                ['militar', 'caixas', 'militar', 'caminho', 'militar', 'caixas', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar', 'militar'],
                ['militar', 'caminho', 'caminho', 'caminho', 'militar', 'obstaculo', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar', 'militar'],
                ['militar', 'militar', 'militar', 'caminho', 'militar', 'militar', 'militar', 'militar'],
            ],
            'player' => ['x' => 3, 'y' => 7, 'direction' => 'back', 'health' => 3, 'maxHealth' => 3],
            'goal' => ['x' => 3, 'y' => 0],
            'requiredItems' => ['handgun'],
            'zombies' => [],
            'collectibles' => [
                ['id' => 'handgun_level_7', 'itemId' => 'handgun', 'x' => 1, 'y' => 5],
            ],
            'scenery' => [
                ['x' => 1, 'y' => 1, 'type' => 'caixas'],
                ['x' => 5, 'y' => 1, 'type' => 'caixas'],
                ['x' => 1, 'y' => 3, 'type' => 'lixo'],
                ['x' => 5, 'y' => 3, 'type' => 'destrocos'],
                ['x' => 5, 'y' => 5, 'type' => 'caixas'],
                ['x' => 2, 'y' => 6, 'type' => 'sangue'],
                ['x' => 4, 'y' => 6, 'type' => 'destrocos'],
            ],
            'atmosphere' => 'deposito',
            'mapIcon' => 'mapa2_armory',
            'mapLocation' => ['x' => 170, 'y' => 430],
        ];
    }
}
