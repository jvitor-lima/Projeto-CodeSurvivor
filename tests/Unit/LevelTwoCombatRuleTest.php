<?php

namespace Tests\Unit;

use App\Game\Engine\GameEngine;
use App\Game\Engine\GameState;
use PHPUnit\Framework\TestCase;

class LevelTwoCombatRuleTest extends TestCase
{
    public function test_level_three_names_the_village_zombie(): void
    {
        $state = new GameState();
        (new GameEngine())->initLevel($state, 3);

        $this->assertCount(1, $state->zombies);
        $this->assertSame('Don José', $state->zombies[0]->name);
        $this->assertSame('don_jose', $state->zombies[0]->type);
        $this->assertSame(1, $state->zombies[0]->damage);
    }

    public function test_don_jose_does_not_hit_before_the_player_can_attack(): void
    {
        $state = $this->runLevelThree(
            <<<'CODE'
            hero.moveDown(2)
            hero.moveRight(2)
            hero.moveDown()
            CODE
        );

        $this->assertFalse($state->win);
        $this->assertFalse($state->lose);
        $this->assertSame(3, $state->player->health);
        $this->assertTrue($state->zombies[0]->activated);
        $this->assertStringContainsString('Don José percebeu o heroi de perto.', implode("\n", $state->log));
    }

    public function test_don_jose_deals_one_damage_after_the_player_ignores_the_warning(): void
    {
        $state = $this->runLevelThree(
            <<<'CODE'
            hero.moveDown(2)
            hero.moveRight(2)
            hero.moveDown()
            hero.moveDown()
            CODE
        );

        $this->assertFalse($state->win);
        $this->assertFalse($state->lose);
        $this->assertSame(2, $state->player->health);
        $this->assertStringContainsString('Don José atacou o heroi! Vida restante: 2', implode("\n", $state->log));
    }

    public function test_don_jose_chases_player_after_being_ignored(): void
    {
        $state = $this->runLevelThree(
            <<<'CODE'
            hero.moveDown(2)
            hero.moveRight(2)
            hero.moveDown()
            hero.moveUp()
            CODE
        );

        $this->assertFalse($state->win);
        $this->assertFalse($state->lose);
        $this->assertSame(3, $state->player->health);
        $this->assertSame(4, $state->zombies[0]->x);
        $this->assertSame(3, $state->zombies[0]->y);
        $this->assertStringContainsString('Don José perseguiu o heroi', implode("\n", $state->log));
    }

    public function test_level_three_loses_only_after_repeated_don_jose_attacks_when_player_ignores_him(): void
    {
        $state = $this->runLevelThree(
            <<<'CODE'
            hero.moveDown(2)
            hero.moveRight(2)
            hero.moveDown()
            hero.moveUp()
            hero.moveUp()
            hero.moveDown()
            hero.moveDown()
            hero.moveUp()
            hero.moveDown()
            CODE
        );

        $this->assertTrue($state->lose);
        $this->assertFalse($state->win);
        $this->assertSame('error', $state->messageType);
        $this->assertSame(0, $state->player->health);
    }

    public function test_level_three_wins_after_zombie_is_killed_with_attack(): void
    {
        $state = $this->runLevelThree(
            <<<'CODE'
            hero.moveDown(2)
            hero.moveRight(2)
            hero.moveDown()
            hero.attack()
            hero.moveDown(4)
            CODE
        );

        $this->assertTrue($state->win);
        $this->assertFalse($state->lose);
        $this->assertSame('success', $state->messageType);
    }

    private function runLevelThree(string $code): GameState
    {
        $state = new GameState();
        $engine = new GameEngine();
        $engine->initLevel($state, 3);

        $queue = $engine->prepareCommands($state, $code);
        $step = 0;
        $done = false;

        for ($guard = 0; ! $done && $guard < 100; $guard++) {
            $result = $engine->step($state, $queue, $step);
            $queue = $result['queue'];
            $step = $result['step'];
            $done = $result['done'];
        }

        $this->assertTrue($done, 'The command queue did not finish.');

        return $state;
    }
}
