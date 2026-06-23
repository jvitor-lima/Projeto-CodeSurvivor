<?php

namespace App\Game\Entities;

/**
 * Entidade que representa um zumbi (inimigo) no jogo.
 */
class Zombie extends Entity
{
    private const ZEALOT_BASE = 'zombie/zealots/';
    private const GANADO_BASE = 'zombie/Ganados/';
    private const GARRADOR_BASE = 'zombie/Garrador/';

    public string $direction;
    public int    $health;
    public int    $maxHealth;
    public int    $damage;
    public ?int   $visionRange = null;
    public bool   $isAlert = false;
    public string $type = 'normal';
    public ?array $patrolPath = null;
    public int    $patrolIndex = 0;
    public int    $waitTurns = 0;
    public bool   $isInteracting = false;
    public bool   $isDying = false;
    public string $name = 'Zumbi';
    public ?int   $activationRange = null;
    public ?array $activationTarget = null;
    public bool   $activated = false;
    public int    $attackCooldown = 0;

    public function __construct(
        int $x = 0,
        int $y = 0,
        string $direction = 'front',
        int $health = 1,
        int $maxHealth = 1,
        int $damage = 1,
        string $id = '',
        ?int $visionRange = null
    ) {
        parent::__construct($x, $y, $id);
        $this->direction   = $direction;
        $this->health      = $health;
        $this->maxHealth   = $maxHealth;
        $this->damage      = $damage;
        $this->visionRange = $visionRange;
    }

    public function isAlive(): bool
    {
        return $this->health > 0;
    }

    public function takeDamage(int $amount): void
    {
        $this->health = max(0, $this->health - $amount);
    }

    public function shouldActivate(int $playerX, int $playerY): bool
    {
        if ($this->activated || $this->activationRange === null || $this->activationTarget === null) {
            return false;
        }

        return max(abs($playerX - $this->x), abs($playerY - $this->y)) <= $this->activationRange;
    }

    public function activate(): bool
    {
        if ($this->activationTarget === null) {
            return false;
        }

        $this->activated = true;
        $this->isInteracting = true;

        return $this->moveOneStepToward($this->activationTarget['x'], $this->activationTarget['y']);
    }

    public function isMovingToActivationTarget(): bool
    {
        return $this->activated
            && $this->activationTarget !== null
            && ($this->x !== $this->activationTarget['x'] || $this->y !== $this->activationTarget['y']);
    }

    public function moveTowardActivationTarget(): bool
    {
        if ($this->activationTarget === null) {
            return false;
        }

        $this->isInteracting = true;

        return $this->moveOneStepToward($this->activationTarget['x'], $this->activationTarget['y']);
    }

    public function moveToward(int $targetX, int $targetY): bool
    {
        $this->isInteracting = true;

        return $this->moveOneStepToward($targetX, $targetY);
    }

    public function isAdjacentTo(int $x, int $y): bool
    {
        return abs($this->x - $x) + abs($this->y - $y) === 1;
    }

    public function isDonJose(): bool
    {
        return $this->type === 'don_jose' || strcasecmp($this->name, 'Don José') === 0 || strcasecmp($this->name, 'Don Jose') === 0;
    }

    public function tickAttackCooldown(): void
    {
        if ($this->attackCooldown > 0) {
            $this->attackCooldown--;
        }
    }

    public function canAttack(): bool
    {
        return $this->attackCooldown <= 0;
    }

    public function recordAttack(int $cooldown = 2): void
    {
        $this->activated = true;
        $this->attackCooldown = $cooldown;
    }

    private function moveOneStepToward(int $targetX, int $targetY): bool
    {
        $oldX = $this->x;
        $oldY = $this->y;

        if ($this->x < $targetX) {
            $this->x++;
            $this->direction = 'right';
        } elseif ($this->x > $targetX) {
            $this->x--;
            $this->direction = 'left';
        } elseif ($this->y < $targetY) {
            $this->y++;
            $this->direction = 'front';
        } elseif ($this->y > $targetY) {
            $this->y--;
            $this->direction = 'back';
        }

        return $this->x !== $oldX || $this->y !== $oldY;
    }

    /**
     * Verifica se o zumbi pode ver o jogador.
     * 
     * Implementa uma visão baseada em Tiles para garantir que o jogador só seja visto
     * se estiver diretamente na linha de visão (ortogonal) ou no cone imediato.
     */
    /**
     * Verifica se o zumbi pode ver uma determinada posição no grid.
     */
    public function canSeePlayer(int $playerX, int $playerY): bool
    {
        $range = (int)($this->visionRange ?? 0);
        if ($range <= 0) {
            return false;
        }

        $dx = $playerX - $this->x;
        $dy = $playerY - $this->y;

        // Distância de Chebyshev (máximo entre eixos) para cobrir o grid NxN corretamente
        if (max(abs($dx), abs($dy)) > $range) {
            return false;
        }

        // Lógica de visão direcional (Linha Reta Perfeita)
        // O zumbi vigia apenas a linha ou coluna exata em que está posicionado.
        switch ($this->direction) {
            case 'front':       return $dy > 0 && $dx === 0;
            case 'back':        return $dy < 0 && $dx === 0;
            case 'left':        return $dx < 0 && $dy === 0;
            case 'right':       return $dx > 0 && $dy === 0;
            case 'front-left':  return $dx < 0 && $dy > 0 && abs($dx) === abs($dy);
            case 'front-right': return $dx > 0 && $dy > 0 && abs($dx) === abs($dy);
            case 'back-left':   return $dx < 0 && $dy < 0 && abs($dx) === abs($dy);
            case 'back-right':  return $dx > 0 && $dy < 0 && abs($dx) === abs($dy);
            default:            return false;
        }
    }

    public function getSprite(): string
    {
        if ($this->isZealot()) {
            return $this->getZealotSprite();
        }

        if ($this->isGanado()) {
            return $this->getGanadoSprite();
        }

        if ($this->isGarrador()) {
            return $this->getGarradorSprite();
        }

        if ($this->type === 'fure') {
            $prefix = 'assets/sprites/zombie_fure_';
            return match ($this->direction) {
                'front'       => $prefix . 'front.png',
                'back'        => $prefix . 'back.png',
                'left'        => $prefix . 'left.png',
                'right'       => $prefix . 'right.png',
                'front-left'  => $prefix . 'left.png',
                'front-right' => $prefix . 'right.png',
                'back-left'   => $prefix . 'left.png',
                'back-right'  => $prefix . 'right.png',
                default       => $prefix . 'front.png',
            };
        }

        $prefix = 'zombie/zombie_';
        return match ($this->direction) {
            'front'       => $prefix . 'front.png',
            'back'        => $prefix . 'back.png',
            'left'        => $prefix . 'left.png',
            'right'       => $prefix . 'right.png',
            'front-left'  => $prefix . 'front-left.png',
            'front-right' => $prefix . 'front-right.png',
            'back-left'   => $prefix . 'back-left.png',
            'back-right'  => $prefix . 'back-right.png',
            default       => $prefix . 'front.png',
        };
    }

    public function isZealot(): bool
    {
        return $this->type === 'zealot' || strcasecmp($this->name, 'zealot') === 0;
    }

    public function isGanado(): bool
    {
        return $this->type === 'ganado' || strcasecmp($this->name, 'Ganado') === 0;
    }

    public function isGarrador(): bool
    {
        return $this->type === 'garrador' || strcasecmp($this->name, 'Garrador') === 0;
    }

    public function hasBrokenShield(): bool
    {
        return $this->isZealot() && $this->isAlive() && $this->health < $this->maxHealth;
    }

    public function getZealotSprite(): string
    {
        if ($this->hasBrokenShield()) {
            return self::ZEALOT_BASE . 'zealot_shield_broken.png';
        }

        return self::ZEALOT_BASE . 'zealot_idle.png';
    }

    public function getGanadoSprite(): string
    {
        return self::GANADO_BASE . match ($this->direction) {
            'back'        => 'back.png',
            'left',
            'front-left',
            'back-left'   => 'left.png',
            'right',
            'front-right',
            'back-right'  => 'right.png',
            default       => 'front.png',
        };
    }

    public function getGarradorSprite(): string
    {
        if ($this->isInteracting) {
            return self::GARRADOR_BASE . match ($this->direction) {
                'right',
                'front-right',
                'back-right' => 'garrador_walk_right.png',
                default      => 'garrador_walk.png',
            };
        }

        return self::GARRADOR_BASE . match ($this->direction) {
            'right',
            'front-right',
            'back-right' => 'garrador_stop_right.png',
            'front',
            'back'       => 'garrador_idle.png',
            default      => 'garrador_stop.png',
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function getZealotWalkSprites(): array
    {
        return [
            self::ZEALOT_BASE . 'zealot_idle.png',
            self::ZEALOT_BASE . 'zealot_walk_1.png',
        ];
    }

    /**
     * Atualiza o estado do zumbi, movendo-o se houver uma patrulha definida.
     */
    /**
     * Atualiza o estado do zumbi, movendo-o se houver uma patrulha definida.
     */
    public function update(): void
    {
        if (!$this->isAlive() || $this->patrolPath === null || empty($this->patrolPath)) {
            return;
        }

        // Se estiver interagindo/esperando, decrementa o contador
        if ($this->waitTurns > 0) {
            $this->waitTurns--;
            $this->isInteracting = true;
            return;
        }

        $this->isInteracting = false;
        $target = $this->patrolPath[$this->patrolIndex];
        
        // Se já está no target, espera um pouco e vai para o próximo
        if ($this->x === $target['x'] && $this->y === $target['y']) {
            $this->waitTurns = 1; // Espera 1 turno no ponto de destino
            $this->isInteracting = true;
            $this->patrolIndex = ($this->patrolIndex + 1) % count($this->patrolPath);
            
            // Inverte a direção visual no ponto de espera
            $nextTarget = $this->patrolPath[$this->patrolIndex];
            if ($nextTarget['x'] > $this->x) $this->direction = 'right';
            elseif ($nextTarget['x'] < $this->x) $this->direction = 'left';
            elseif ($nextTarget['y'] > $this->y) $this->direction = 'front';
            elseif ($nextTarget['y'] < $this->y) $this->direction = 'back';
            
            return;
        }

        // Move um passo em direção ao target
        $this->moveOneStepToward($target['x'], $target['y']);
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'x'           => $this->x,
            'y'           => $this->y,
            'direction'   => $this->direction,
            'health'      => $this->health,
            'maxHealth'   => $this->maxHealth,
            'damage'      => $this->damage,
            'visionRange' => $this->visionRange,
            'isAlert'     => $this->isAlert,
            'type'          => $this->type,
            'patrolPath'    => $this->patrolPath,
            'patrolIndex'   => $this->patrolIndex,
            'waitTurns'     => $this->waitTurns,
            'isInteracting' => $this->isInteracting,
            'isDying'       => $this->isDying,
            'name'             => $this->name,
            'activationRange'  => $this->activationRange,
            'activationTarget' => $this->activationTarget,
            'activated'        => $this->activated,
            'attackCooldown'   => $this->attackCooldown,
        ];
    }

    public static function fromArray(array $data): static
    {
        $zombie = new static(
            x:           $data['x'] ?? 0,
            y:           $data['y'] ?? 0,
            direction:   $data['direction'] ?? 'front',
            health:      $data['health'] ?? 1,
            maxHealth:   $data['maxHealth'] ?? 1,
            damage:      $data['damage'] ?? 1,
            id:          $data['id'] ?? '',
            visionRange: $data['visionRange'] ?? null,
        );
        $zombie->isAlert       = $data['isAlert'] ?? false;
        $zombie->type          = $data['type'] ?? 'normal';
        $zombie->patrolPath    = $data['patrolPath'] ?? null;
        $zombie->patrolIndex   = $data['patrolIndex'] ?? 0;
        $zombie->waitTurns     = $data['waitTurns'] ?? 0;
        $zombie->isInteracting = $data['isInteracting'] ?? false;
        $zombie->isDying       = $data['isDying'] ?? false;
        $zombie->name             = $data['name'] ?? 'Zumbi';
        $zombie->activationRange  = $data['activationRange'] ?? null;
        $zombie->activationTarget = $data['activationTarget'] ?? null;
        $zombie->activated        = $data['activated'] ?? false;
        $zombie->attackCooldown   = $data['attackCooldown'] ?? 0;
        return $zombie;
    }
}
