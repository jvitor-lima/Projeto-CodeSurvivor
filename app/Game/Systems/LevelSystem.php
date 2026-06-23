<?php

namespace App\Game\Systems;

use App\Game\Config\LevelConfig;
use App\Game\Engine\GameState;
use App\Game\Entities\Goal;
use App\Game\Entities\Player;
use App\Game\Entities\Zombie;
use InvalidArgumentException;

/**
 * Sistema responsável por carregar e configurar as fases do jogo.
 */
class LevelSystem
{
    public function loadLevel(GameState $state, int $level): void
    {
        $existingInventory = $state->player->inventory ?? [];

        $state->win       = false;
        $state->lose      = false;
        $state->loseModalDelayMs = 0;
        $state->isRunning = false;
        $state->log       = [];
        $state->clearMessage();

        $config = LevelConfig::getLevelBySequence($level);

        $state->level     = $level;
        $state->gridSize  = $config['gridSize'];
        $state->map       = $config['map'];
        $state->phaseType = $config['phaseType'];
        $state->initialCode = $config['initialCode'] ?? '';
        $state->player    = new Player(
            x:         $config['player']['x'],
            y:         $config['player']['y'],
            direction: $config['player']['direction'] ?? 'front',
            health:    $config['player']['health']    ?? 3,
            maxHealth: $config['player']['maxHealth'] ?? 3,
            inventory: $config['player']['inventory'] ?? $existingInventory,
        );

        if ($level >= 7) {
            $state->player->useHandgunSpriteSet();
        } elseif ($level <= 2) {
            $state->player->useDefaultSpriteSet();
        } else {
            $state->player->useStaffSpriteSetIfEquipped();
        }

        $state->goal = new Goal(
            x: $config['goal']['x'],
            y: $config['goal']['y'],
        );
        $state->goal->asset = $config['goal']['asset'] ?? null;

        $state->zombies = array_map(
            function (array $z) {
                $zombie = new Zombie(
                    x:           $z['x'],
                    y:           $z['y'],
                    direction:   $z['direction'] ?? 'front',
                    health:      $z['health']    ?? 1,
                    maxHealth:   $z['maxHealth'] ?? 1,
                    damage:      $z['damage']    ?? 1,
                    visionRange: $z['visionRange'] ?? null,
                );
                $zombie->type        = $z['type'] ?? 'normal';
                $zombie->patrolPath  = $z['patrolPath'] ?? null;
                $zombie->patrolIndex = $z['patrolIndex'] ?? 0;
                $zombie->name        = $z['name'] ?? 'Zumbi';
                $zombie->activationRange  = $z['activationRange'] ?? null;
                $zombie->activationTarget = $z['activationTarget'] ?? null;
                return $zombie;
            },
            $config['zombies']
        );

        // Carrega cenario e atmosfera
        $state->scenery    = $config['scenery']    ?? [];
        $state->collectibles = $this->filterCollectedItems($config['collectibles'] ?? [], $state->player->inventory);
        $state->requiredItems = $config['requiredItems'] ?? [];
        $state->atmosphere = $config['atmosphere'] ?? 'default';
        $state->objective  = $config['objective']  ?? '';

        $state->addLog("Fase {$level} iniciada: {$config['name']}");
    }

    public function checkWin(GameState $state): bool
    {
        $player = $state->player;
        $goal   = $state->goal;

        if ($player->x === $goal->x && $player->y === $goal->y) {
            $missingItems = $this->missingRequiredItems($state);

            if ($missingItems !== []) {
                $state->isRunning = false;
                $state->setMessage('Falta um item obrigatorio. Colete antes de sair da fase.', 'error');
                $state->addLog('OBJETIVO INCOMPLETO: Leon chegou a saida sem coletar o item obrigatorio.');
                return false;
            }

            if ($state->phaseType === 'combate_linear' && $this->hasAliveZombies($state)) {
                $state->addLog('Objetivo em espera: neutralize todos os inimigos antes de sair.');
                return false;
            }

            if ($state->phaseType === 'combate' && $this->hasAliveZombies($state)) {
                $state->lose      = true;
                $state->isRunning = false;
                $state->setMessage('O caminho ainda nao esta seguro. Use hero.attack() antes de sair.', 'error');
                $state->addLog('DERROTA: Leon chegou ao objetivo sem eliminar todos os inimigos.');
                return true;
            }

            $state->win       = true;
            $state->isRunning = false;
            $state->setMessage('Fase concluida! Boa rota.', 'success');
            $state->addLog('VITORIA: Leon chegou ao objetivo.');
            return true;
        }

        return false;
    }

    private function hasAliveZombies(GameState $state): bool
    {
        foreach ($state->zombies as $zombie) {
            if ($zombie->isAlive()) {
                return true;
            }
        }

        return false;
    }

    private function missingRequiredItems(GameState $state): array
    {
        return array_values(array_filter(
            $state->requiredItems,
            fn (string $itemId) => ! $state->player->hasItem($itemId)
        ));
    }

    private function filterCollectedItems(array $collectibles, array $inventory): array
    {
        return array_values(array_filter(
            $collectibles,
            fn (array $collectible) => ! isset($inventory[(string) ($collectible['itemId'] ?? $collectible['id'] ?? '')])
        ));
    }
}
