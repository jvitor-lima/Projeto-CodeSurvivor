<?php

namespace App\Game\Systems;

use App\Game\Engine\GameState;
use App\Game\Entities\Zombie;

/**
 * Sistema responsável por toda lógica de combate do jogo.
 *
 * O CombatSystem processa ataques do jogador contra zumbis,
 * verifica colisões (zumbi no mesmo tile que o jogador) e
 * aplica dano bidirecional.
 *
 * Ele também é responsável por remover entidades mortas do estado.
 */
class CombatSystem
{
    /**
     * Executa o ataque do jogador na direção em que ele está olhando.
     *
     * Calcula o tile-alvo com base na direção do jogador e remove qualquer
     * zumbi que esteja nessa posição. Futuramente, aplicará dano em vez de
     * remoção instantânea.
     *
     * @param GameState $state Estado atual do jogo.
     * @return bool            Retorna true se algum zumbi foi atingido.
     */
    public function playerAttack(GameState $state, ?string $targetName = null): bool
    {
        $player = $state->player;
        $hit    = false;

        // Define as 4 direções adjacentes (Up, Down, Left, Right)
        $adjacentOffsets = [
            [0, -1], // Up
            [0, 1],  // Down
            [-1, 0], // Left
            [1, 0],  // Right
        ];

        foreach ($state->zombies as $zombie) {
            if (! $this->targetMatches($zombie, $targetName)) {
                continue;
            }

            $isAdjacent = false;
            foreach ($adjacentOffsets as $offset) {
                if ($zombie->x === $player->x + $offset[0] && $zombie->y === $player->y + $offset[1]) {
                    $isAdjacent = true;
                    break;
                }
            }

            if ($isAdjacent) {
                if ($zombie->isGarrador()) {
                    $player->takeDamage($player->health);
                    $state->lose = true;
                    $state->isRunning = false;
                    $state->shake = true;
                    $state->loseModalDelayMs = 1800;
                    $state->setMessage('O Garrador nao pode ser derrotado de perto. Fuja do campo de visao.', 'error');
                    $state->addLog('DERROTA: Leon tentou enfrentar o Garrador de perto.');
                    return true;
                }

                $zombie->takeDamage(1);
                $hit = true;
                
                if (! $zombie->isAlive()) {
                    $zombie->isDying = true;
                    $state->shake    = true;
                    $state->addLog("{$zombie->name} eliminado em ({$zombie->x}, {$zombie->y})!");
                } else {
                    $state->addLog("{$zombie->name} atingido em ({$zombie->x}, {$zombie->y})! Vida: {$zombie->health}");
                }
            }
        }

        if (! $hit) {
            $targetLabel = $targetName !== null ? " {$targetName}" : '';
            $state->addLog("Ataque falhou: nenhum alvo{$targetLabel} adjacente.");
        }

        return $hit;
    }

    private function targetMatches(Zombie $zombie, ?string $targetName): bool
    {
        if ($targetName === null) {
            return true;
        }

        $targetName = trim($targetName);

        if ($targetName === '') {
            return true;
        }

        return strcasecmp($zombie->name, $targetName) === 0
            || strcasecmp($zombie->type, $targetName) === 0;
    }

    /**
     * Verifica se algum zumbi está no mesmo tile que o jogador (colisão de contato).
     *
     * Se houver colisão, aplica dano ao jogador e registra no log.
     *
     * @param GameState $state Estado atual do jogo.
     * @return bool            Retorna true se houve colisão.
     */
    public function checkZombieCollision(GameState $state): bool
    {
        $player = $state->player;

        foreach ($state->zombies as $zombie) {
            if (! $zombie->isAlive()) {
                continue;
            }

            if ($zombie->x === $player->x && $zombie->y === $player->y) {
                if (($state->phaseType === 'combate' || $state->phaseType === 'combate_linear') && ! $zombie->isDonJose()) {
                    $state->lose      = true;
                    $state->isRunning = false;
                    $state->setMessage('Leon tentou passar por um inimigo vivo. Use hero.attack() para abrir caminho.', 'error');
                    $state->addLog('DERROTA: Leon ignorou o inimigo da fase de combate.');
                    return true;
                }

                if (! $zombie->canAttack()) {
                    return false;
                }

                $player->takeDamage($zombie->damage);
                $zombie->recordAttack();
                $state->addLog("{$zombie->name} atacou Leon. Vida restante: {$player->health}");
                return true;
            }
        }

        return false;
    }

    public function processDonJosePressure(GameState $state): bool
    {
        $player = $state->player;
        $acted = false;

        foreach ($state->zombies as $zombie) {
            if (! $zombie->isAlive() || ! $zombie->isDonJose()) {
                continue;
            }

            $zombie->tickAttackCooldown();

            if ($zombie->isAdjacentTo($player->x, $player->y)) {
                if (! $zombie->activated) {
                    $zombie->activated = true;
                    $zombie->attackCooldown = max($zombie->attackCooldown, 1);
                    $state->addLog("{$zombie->name} percebeu Leon de perto.");
                    $acted = true;
                    continue;
                }

                if ($zombie->canAttack()) {
                    $player->takeDamage($zombie->damage);
                    $zombie->recordAttack();
                    $state->shake = true;
                    $state->addLog("{$zombie->name} atacou de perto. Vida restante: {$player->health}");
                    $acted = true;
                }

                continue;
            }

            if ($zombie->activated) {
                $oldX = $zombie->x;
                $oldY = $zombie->y;

                if ($zombie->moveToward($player->x, $player->y)) {
                    $state->addLog("{$zombie->name} perseguiu Leon de ({$oldX}, {$oldY}) para ({$zombie->x}, {$zombie->y}).");
                    $acted = true;
                }
            }
        }

        return $acted;
    }

    /**
     * Verifica se o jogador está morto e atualiza o estado do jogo.
     *
     * @param GameState $state Estado atual do jogo.
     * @return bool            Retorna true se o jogador morreu.
     */
    public function checkPlayerDeath(GameState $state): bool
    {
        if (! $state->player->isAlive()) {
            $state->lose     = true;
            $state->isRunning = false;
            $state->setMessage('Leon morreu. Ajuste sua sequencia e tente novamente.', 'error');
            $state->addLog('GAME OVER: Leon foi derrotado.');
            return true;
        }

        return false;
    }

    /**
     * Calcula o tile-alvo de um ataque com base na posição e direção do atacante.
     *
     * @param  int    $x         Posição X do atacante.
     * @param  int    $y         Posição Y do atacante.
     * @param  string $direction Direção do atacante.
     * @return array{int, int}   [targetX, targetY]
     */
    public function getAttackTarget(int $x, int $y, string $direction): array
    {
        return match ($direction) {
            'front'       => [$x,     $y + 1],
            'back'        => [$x,     $y - 1],
            'front-left'  => [$x - 1, $y],
            'front-right' => [$x + 1, $y],
            'back-left'   => [$x - 1, $y - 1],
            'back-right'  => [$x + 1, $y - 1],
            default       => [$x,     $y + 1],
        };
    }
}
