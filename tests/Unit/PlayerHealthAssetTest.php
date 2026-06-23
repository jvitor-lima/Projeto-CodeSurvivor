<?php

namespace Tests\Unit;

use App\Livewire\GameBoard;
use PHPUnit\Framework\TestCase;

class PlayerHealthAssetTest extends TestCase
{
    public function test_player_health_assets_match_three_health_states(): void
    {
        $component = new GameBoard();

        $component->gameState = ['player' => ['health' => 3, 'maxHealth' => 3]];
        $this->assertSame('hud/health.png', $component->getPlayerHealthAsset());

        $component->gameState = ['player' => ['health' => 2, 'maxHealth' => 3]];
        $this->assertSame('hud/health_medium.png', $component->getPlayerHealthAsset());

        $component->gameState = ['player' => ['health' => 1, 'maxHealth' => 3]];
        $this->assertSame('hud/health_low.png', $component->getPlayerHealthAsset());
    }
}
