<?php

namespace Tests\Unit;

use App\Livewire\GameBoard;
use PHPUnit\Framework\TestCase;

class GameBoardInventoryTest extends TestCase
{
    public function test_inventory_uses_eight_slots_by_default(): void
    {
        $component = new GameBoard();
        $component->gameState = [
            'player' => [
                'inventory' => [
                    'bastao' => [
                        'id' => 'bastao',
                        'name' => 'Bastao de Defesa',
                    ],
                ],
            ],
        ];

        $this->assertSame(8, $component->getInventorySlotCount());
        $this->assertCount(8, $component->getInventorySlots());
    }

    public function test_inventory_items_can_move_to_empty_slots(): void
    {
        $component = new GameBoard();
        $component->gameState = [
            'player' => [
                'inventory' => [
                    'bastao' => [
                        'id' => 'bastao',
                        'name' => 'Bastao de Defesa',
                    ],
                    'handgun' => [
                        'id' => 'handgun',
                        'name' => 'Pistola',
                    ],
                ],
            ],
        ];

        $component->moveInventoryItem(0, 5);

        $slots = $component->getInventorySlots();

        $this->assertNull($slots[0]);
        $this->assertSame('handgun', $slots[1]['id']);
        $this->assertSame('bastao', $slots[5]['id']);
        $this->assertSame(5, $component->gameState['player']['inventory']['bastao']['slot']);
    }

    public function test_inventory_items_can_swap_slots(): void
    {
        $component = new GameBoard();
        $component->gameState = [
            'player' => [
                'inventory' => [
                    'bastao' => [
                        'id' => 'bastao',
                        'name' => 'Bastao de Defesa',
                        'slot' => 0,
                    ],
                    'handgun' => [
                        'id' => 'handgun',
                        'name' => 'Pistola',
                        'slot' => 3,
                    ],
                ],
            ],
        ];

        $component->moveInventoryItem(0, 3);

        $slots = $component->getInventorySlots();

        $this->assertSame('handgun', $slots[0]['id']);
        $this->assertSame('bastao', $slots[3]['id']);
        $this->assertSame(3, $component->gameState['player']['inventory']['bastao']['slot']);
        $this->assertSame(0, $component->gameState['player']['inventory']['handgun']['slot']);
    }
}
