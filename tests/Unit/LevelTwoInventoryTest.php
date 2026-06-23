<?php

namespace Tests\Unit;

use App\Game\Engine\GameEngine;
use App\Game\Engine\GameState;
use PHPUnit\Framework\TestCase;

class LevelTwoInventoryTest extends TestCase
{
    public function test_level_two_collects_staff_and_updates_player_sprite(): void
    {
        $state = new GameState();
        $engine = new GameEngine();
        $engine->initLevel($state, 2);

        $this->assertSame('personagem/idle_up.png', $state->player->getIdleSprite());
        $this->assertSame(1, $state->collectibles[0]['x']);
        $this->assertSame(5, $state->collectibles[0]['y']);
        $this->assertCount(1, $state->collectibles);

        $queue = $engine->prepareCommands(
            $state,
            <<<'CODE'
            hero.moveUp(2)
            hero.moveLeft(2)
            CODE
        );
        $step = 0;
        $done = false;

        for ($guard = 0; ! $done && $guard < 20; $guard++) {
            $result = $engine->step($state, $queue, $step);
            $queue = $result['queue'];
            $step = $result['step'];
            $done = $result['done'];
        }

        $this->assertArrayHasKey('bastao', $state->player->inventory);
        $this->assertTrue($state->player->inventory['bastao']['collected']);
        $this->assertTrue($state->player->inventory['bastao']['equipped']);
        $this->assertSame('personagem/person_bastao/idle_left.png', $state->player->getIdleSprite());
        $this->assertCount(0, $state->collectibles);
    }

    public function test_level_two_does_not_complete_when_staff_is_ignored(): void
    {
        $state = new GameState();
        $engine = new GameEngine();
        $engine->initLevel($state, 2);

        $queue = $engine->prepareCommands($state, 'hero.moveUp(7)');
        $step = 0;
        $done = false;

        for ($guard = 0; ! $done && $guard < 20; $guard++) {
            $result = $engine->step($state, $queue, $step);
            $queue = $result['queue'];
            $step = $result['step'];
            $done = $result['done'];
        }

        $this->assertTrue($done, 'The command queue did not finish.');
        $this->assertFalse($state->win);
        $this->assertFalse($state->lose);
        $this->assertSame('error', $state->messageType);
        $this->assertStringContainsString('item obrigatorio', $state->message);
    }

    public function test_next_levels_use_staff_sprite_when_staff_is_in_inventory(): void
    {
        $state = new GameState();
        $state->player->addItem([
            'id' => 'bastao',
            'name' => 'Bastao de Defesa',
            'type' => 'weapon',
            'sprite' => 'itens/bastao.png',
            'collected' => true,
            'equipped' => true,
        ]);

        (new GameEngine())->initLevel($state, 3);

        $this->assertSame('personagem/person_bastao/idle_down.png', $state->player->getIdleSprite());
    }
}
