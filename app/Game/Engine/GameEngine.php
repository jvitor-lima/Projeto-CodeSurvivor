<?php

namespace App\Game\Engine;

use App\Game\Commands\Command;
use App\Game\Services\CommandParserService;
use App\Game\Systems\CombatSystem;
use App\Game\Systems\LevelSystem;

/**
 * Motor central do jogo (GameEngine).
 */
class GameEngine
{
    private LevelSystem          $levelSystem;
    private CombatSystem         $combatSystem;
    private CommandParserService $parserService;

    public function __construct()
    {
        $this->levelSystem   = new LevelSystem();
        $this->combatSystem  = new CombatSystem();
        $this->parserService = new CommandParserService();
    }

    public function initLevel(GameState $state, int $level): void
    {
        $this->levelSystem->loadLevel($state, $level);
    }

    public function prepareCommands(GameState $state, string $code): array
    {
        $commands = $this->parserService->parse($code);

        if ($this->parserService->hasErrors()) {
            $state->setMessage($this->parserService->getFirstError(), 'error');
            $state->isRunning = false;
            return [];
        }

        if (empty($commands)) {
            $state->setMessage('Nenhum comando para executar.', 'info');
            return [];
        }

        $state->isRunning = true;
        $state->clearMessage();
        $state->addLog('Execução iniciada: ' . count($commands) . ' comando(s).');

        return $this->parserService->serializeCommands($commands);
    }

    public function step(GameState $state, array $serializedQueue, int $currentStep): array
    {
        // Limpa zumbis que já morreram no turno anterior
        $state->zombies = array_values(
            array_filter($state->zombies, fn($z) => !($z->isDying))
        );
        
        // Reseta efeitos visuais de transição
        $state->shake = false;

        if (! $state->isRunning || ! isset($serializedQueue[$currentStep])) {
            $state->isRunning = false;
            return ['queue' => $serializedQueue, 'step' => $currentStep, 'done' => true];
        }

        $commandData = $serializedQueue[$currentStep];
        $commands    = $this->parserService->deserializeCommands([$commandData]);

        if (! empty($commands)) {
            $command = $commands[0];
            $state->addLog("Executando: {$command->getLabel()} (linha {$commandData['sourceLine']})");
            $command->execute($state);

            if ($state->lose || ! $state->isRunning) {
                return ['queue' => $serializedQueue, 'step' => $currentStep, 'done' => true];
            }
        }

        foreach ($state->zombies as $zombie) {
            if (! $zombie->patrolPath) {
                $zombie->isInteracting = false;
            }
        }

        $activatedThisStep = [];

        foreach ($state->zombies as $zombie) {
            if ($zombie->isAlive() && $zombie->shouldActivate($state->player->x, $state->player->y)) {
                $moved = $zombie->activate();
                $activatedThisStep[$zombie->id] = true;
                $movementText = $moved ? " moveu para ({$zombie->x}, {$zombie->y})" : ' entrou em alerta';
                $state->addLog("{$zombie->name}{$movementText}.");
            }
        }

        foreach ($state->zombies as $zombie) {
            if ($zombie->isAlive() && $zombie->isMovingToActivationTarget() && ! isset($activatedThisStep[$zombie->id])) {
                $zombie->moveTowardActivationTarget();
                $state->addLog("{$zombie->name} avancou pelo corredor para ({$zombie->x}, {$zombie->y}).");
            }
        }

        // Atualiza o movimento dos zumbis (patrulha)
        foreach ($state->zombies as $zombie) {
            if ($zombie->isAlive() && $zombie->patrolPath) {
                $oldX = $zombie->x;
                $oldY = $zombie->y;
                $zombie->update();
                
                if ($zombie->x !== $oldX || $zombie->y !== $oldY) {
                    $state->addLog("Garrador patrulhou para ({$zombie->x}, {$zombie->y})");
                } elseif ($zombie->isInteracting) {
                    $state->addLog("Garrador esta procurando Leon...");
                }
            }
        }

        // Verifica detecção de visão em fases de stealth
        if ($this->combatSystem->processDonJosePressure($state)) {
            if ($this->combatSystem->checkPlayerDeath($state)) {
                return ['queue' => $serializedQueue, 'step' => $currentStep, 'done' => true];
            }
        }

        if ($state->phaseType === 'stealth') {
            foreach ($state->zombies as $zombie) {
                if ($zombie->isAlive() && $zombie->canSeePlayer($state->player->x, $state->player->y)) {
                    $state->lose = true;
                    $state->isRunning = false;
                    $state->setMessage('O Garrador detectou Leon. Evite as casas marcadas.', 'error');
                    $state->addLog("DERROTA: Leon entrou no campo de visao em ({$state->player->x}, {$state->player->y}).");
                    return ['queue' => $serializedQueue, 'step' => $currentStep, 'done' => true];
                }
            }
        }

        if ($this->combatSystem->checkZombieCollision($state)) {
            if ($state->lose) {
                return ['queue' => $serializedQueue, 'step' => $currentStep, 'done' => true];
            }

            if ($this->combatSystem->checkPlayerDeath($state)) {
                return ['queue' => $serializedQueue, 'step' => $currentStep, 'done' => true];
            }
        }

        if ($this->levelSystem->checkWin($state)) {
            return ['queue' => $serializedQueue, 'step' => $currentStep, 'done' => true];
        }

        $nextStep = $currentStep + 1;

        if (! isset($serializedQueue[$nextStep])) {
            $state->isRunning = false;
            $state->addLog('Todos os comandos foram executados.');
            return ['queue' => $serializedQueue, 'step' => $nextStep, 'done' => true];
        }

        return ['queue' => $serializedQueue, 'step' => $nextStep, 'done' => false];
    }



    public function resetLevel(GameState $state): void
    {
        $this->levelSystem->loadLevel($state, $state->level);
    }

    public function nextLevel(GameState $state): bool
    {
        $nextLevel = $state->level + 1;

        try {
            $this->levelSystem->loadLevel($state, $nextLevel);
            return true;
        } catch (\InvalidArgumentException) {
            $state->setMessage('Você completou todas as fases disponíveis!', 'success');
            return false;
        }
    }
}
