<?php

namespace App\Game\Entities;

/**
 * Entidade que representa o heroi controlado pelo jogador.
 */
class Player extends Entity
{
    private const SPRITE_SET_DEFAULT = 'survivor';
    private const SPRITE_SET_STAFF = 'survivor_bastao';
    private const SPRITE_SET_HANDGUN = 'survivor_handgun';

    private const SPRITES = [
        self::SPRITE_SET_DEFAULT => [
            'idle' => [
                'up' => 'personagem/idle_up.png',
                'left' => 'personagem/person_view-front-left.png',
                'right' => 'personagem/person_view-front-right.png',
                'down' => 'personagem/person_view-front.png',
            ],
            'walk' => [
                'up' => ['personagem/walk_up_1.png', 'personagem/walk_up_2.png'],
                'left' => ['personagem/wal_left_1.png', 'personagem/wal_left_2.png'],
                'right' => ['personagem/wal_right_1.png', 'personagem/wal_right_2.png'],
                'down' => ['personagem/walk_down_1.png', 'personagem/walk_down_2.png'],
            ],
        ],
        self::SPRITE_SET_STAFF => [
            'idle' => [
                'up' => 'personagem/person_bastao/idle_up.png',
                'left' => 'personagem/person_bastao/idle_left.png',
                'right' => 'personagem/person_bastao/idle_right.png',
                'down' => 'personagem/person_bastao/idle_down.png',
            ],
            'walk' => [
                'up' => ['personagem/person_bastao/walk_up_1.png', 'personagem/person_bastao/walk_up_2.png'],
                'left' => ['personagem/person_bastao/walk_left_1.png', 'personagem/person_bastao/walk_left_2.png'],
                'right' => ['personagem/person_bastao/walk_right_1.png', 'personagem/person_bastao/walk_right_2.png'],
                'down' => ['personagem/person_bastao/walk_down_1.png', 'personagem/person_bastao/walk_down_2.png'],
            ],
        ],
        self::SPRITE_SET_HANDGUN => [
            'idle' => [
                'up' => 'personagem/person_pistola/backwards.png',
                'left' => 'personagem/person_pistola/left.png',
                'right' => 'personagem/person_pistola/right.png',
                'down' => 'personagem/person_pistola/front.png',
            ],
            'walk' => [
                'up' => ['personagem/person_pistola/backwards.png', 'personagem/person_pistola/backwards.png'],
                'left' => ['personagem/person_pistola/walk_left.png', 'personagem/person_pistola/left.png'],
                'right' => ['personagem/person_pistola/walk_right.png', 'personagem/person_pistola/right.png'],
                'down' => ['personagem/person_pistola/front.png', 'personagem/person_pistola/front.png'],
            ],
        ],
    ];

    public string $direction;

    public int $health;

    public int $maxHealth;

    /**
     * Itens coletados indexados por id.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $inventory;

    public string $spriteSet;

    public function __construct(
        int $x = 0,
        int $y = 0,
        string $direction = 'front',
        int $health = 3,
        int $maxHealth = 3,
        array $inventory = [],
        string $spriteSet = self::SPRITE_SET_DEFAULT,
        string $id = 'player'
    ) {
        parent::__construct($x, $y, $id);
        $this->direction = $direction;
        $this->health = $health;
        $this->maxHealth = $maxHealth;
        $this->inventory = $this->normalizeInventory($inventory);
        $this->spriteSet = array_key_exists($spriteSet, self::SPRITES) ? $spriteSet : self::SPRITE_SET_DEFAULT;
    }

    public function isAlive(): bool
    {
        return $this->health > 0;
    }

    public function takeDamage(int $amount): void
    {
        $this->health = max(0, $this->health - $amount);
    }

    /**
     * @param array<string, mixed> $item
     */
    public function addItem(array $item): void
    {
        if (! isset($item['id'])) {
            return;
        }

        $item['collected'] = $item['collected'] ?? true;
        $item['equipped'] = $item['equipped'] ?? false;
        $this->inventory[(string) $item['id']] = $item;
    }

    public function hasItem(string $itemId): bool
    {
        return isset($this->inventory[$itemId]) && ($this->inventory[$itemId]['collected'] ?? false);
    }

    public function equipItem(string $itemId): void
    {
        if (! isset($this->inventory[$itemId])) {
            return;
        }

        $this->inventory[$itemId]['equipped'] = true;

        if ($itemId === 'handgun') {
            $this->spriteSet = self::SPRITE_SET_HANDGUN;
            return;
        }

        if ($itemId === 'bastao' && $this->spriteSet !== self::SPRITE_SET_HANDGUN) {
            $this->spriteSet = self::SPRITE_SET_STAFF;
        }
    }

    public function useDefaultSpriteSet(): void
    {
        $this->spriteSet = self::SPRITE_SET_DEFAULT;
    }

    public function useHandgunSpriteSet(): void
    {
        $this->spriteSet = self::SPRITE_SET_HANDGUN;
    }

    public function useStaffSpriteSetIfEquipped(): void
    {
        if ($this->hasItem('handgun') && ($this->inventory['handgun']['equipped'] ?? false)) {
            $this->spriteSet = self::SPRITE_SET_HANDGUN;
            return;
        }

        if ($this->hasItem('bastao') && ($this->inventory['bastao']['equipped'] ?? false)) {
            $this->spriteSet = self::SPRITE_SET_STAFF;
            return;
        }

        $this->spriteSet = self::SPRITE_SET_DEFAULT;
    }

    public function getSprite(): string
    {
        return $this->getIdleSprite();
    }

    public function getFacingDirection(): string
    {
        return match ($this->direction) {
            'back', 'back-left', 'back-right', 'up' => 'up',
            'front-left', 'left' => 'left',
            'front-right', 'right' => 'right',
            default => 'down',
        };
    }

    public function getIdleSprite(): string
    {
        return self::SPRITES[$this->spriteSet]['idle'][$this->getFacingDirection()]
            ?? self::SPRITES[self::SPRITE_SET_DEFAULT]['idle']['down'];
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function getWalkSprites(): array
    {
        return self::SPRITES[$this->spriteSet]['walk'][$this->getFacingDirection()]
            ?? self::SPRITES[self::SPRITE_SET_DEFAULT]['walk']['down'];
    }

    public function getWalkSprite(int $frame): string
    {
        return match ($frame) {
            2 => $this->getWalkSprites()[1],
            default => $this->getWalkSprites()[0],
        };
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'x' => $this->x,
            'y' => $this->y,
            'direction' => $this->direction,
            'health' => $this->health,
            'maxHealth' => $this->maxHealth,
            'inventory' => $this->inventory,
            'spriteSet' => $this->spriteSet,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            x: $data['x'] ?? 0,
            y: $data['y'] ?? 0,
            direction: $data['direction'] ?? 'front',
            health: $data['health'] ?? 3,
            maxHealth: $data['maxHealth'] ?? 3,
            inventory: $data['inventory'] ?? [],
            spriteSet: $data['spriteSet'] ?? self::SPRITE_SET_DEFAULT,
            id: $data['id'] ?? 'player',
        );
    }

    private function normalizeInventory(array $inventory): array
    {
        $normalized = [];

        foreach ($inventory as $key => $item) {
            if (is_string($item)) {
                $normalized[$item] = [
                    'id' => $item,
                    'name' => $item,
                    'type' => 'item',
                    'sprite' => '',
                    'collected' => true,
                    'equipped' => false,
                ];
                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $id = (string) ($item['id'] ?? $key);
            $item['id'] = $id;
            $item['collected'] = $item['collected'] ?? true;
            $item['equipped'] = $item['equipped'] ?? false;
            $normalized[$id] = $item;
        }

        return $normalized;
    }
}
