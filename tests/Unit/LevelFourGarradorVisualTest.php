<?php

namespace Tests\Unit;

use App\Game\Engine\GameEngine;
use App\Game\Engine\GameState;
use App\Game\Systems\CombatSystem;
use PHPUnit\Framework\TestCase;

class LevelFourGarradorVisualTest extends TestCase
{
    public function test_level_four_uses_garrador_identity_and_sprite(): void
    {
        $state = new GameState();
        (new GameEngine())->initLevel($state, 4);

        $this->assertCount(1, $state->zombies);

        $garrador = $state->zombies[0];

        $this->assertSame('Garrador', $garrador->name);
        $this->assertSame('garrador', $garrador->type);
        $this->assertTrue($garrador->isGarrador());
        $this->assertSame('zombie/Garrador/garrador_stop.png', $garrador->getSprite());

        $garrador->direction = 'right';
        $this->assertSame('zombie/Garrador/garrador_stop_right.png', $garrador->getSprite());

        $garrador->isInteracting = true;
        $this->assertSame('zombie/Garrador/garrador_walk_right.png', $garrador->getSprite());
    }

    public function test_attacking_adjacent_garrador_kills_player_and_delays_failure_modal(): void
    {
        $state = new GameState();
        (new GameEngine())->initLevel($state, 4);

        $state->isRunning = true;
        $state->player->x = 7;
        $state->player->y = 4;
        $state->zombies[0]->x = 8;
        $state->zombies[0]->y = 4;

        $hit = (new CombatSystem())->playerAttack($state, 'Garrador');

        $this->assertTrue($hit);
        $this->assertTrue($state->lose);
        $this->assertFalse($state->isRunning);
        $this->assertSame(0, $state->player->health);
        $this->assertSame(1800, $state->loseModalDelayMs);
        $this->assertSame('error', $state->messageType);
        $this->assertStringContainsString('Garrador é imortal', $state->message);
        $this->assertTrue($state->zombies[0]->isAlive());
    }
}
