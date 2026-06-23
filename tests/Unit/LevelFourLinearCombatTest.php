<?php

namespace Tests\Unit;

use App\Game\Engine\GameEngine;
use App\Game\Engine\GameState;
use PHPUnit\Framework\TestCase;

class LevelFourLinearCombatTest extends TestCase
{
    public function test_level_five_starts_with_prefilled_code_and_named_enemies(): void
    {
        $state = new GameState();
        (new GameEngine())->initLevel($state, 5);

        $this->assertSame(
            "hero.moveRight()\nhero.attack(\"zealot\")\nhero.attack(\"zealot\")",
            $state->initialCode
        );

        $this->assertSame(0, $state->player->x);
        $this->assertSame(7, $state->goal->x);
        $this->assertSame(['zealot', 'Ganado'], array_map(fn ($zombie) => $zombie->name, $state->zombies));
        $this->assertSame([2, 1], array_map(fn ($zombie) => $zombie->maxHealth, $state->zombies));
        $this->assertSame('zealot', $state->zombies[0]->type);
        $this->assertSame('zombie/zealots/zealot_idle.png', $state->zombies[0]->getSprite());
        $this->assertSame('ganado', $state->zombies[1]->type);
        $this->assertTrue($state->zombies[1]->isGanado());
        $this->assertSame('zombie/Ganados/back.png', $state->zombies[1]->getSprite());
    }

    public function test_zealot_shows_broken_shield_after_first_attack(): void
    {
        $state = $this->runLevelFour(
            <<<'CODE'
            hero.moveRight()
            hero.attack("zealot")
            CODE
        );

        $zealot = $state->zombies[0];

        $this->assertSame('zealot', $zealot->name);
        $this->assertTrue($zealot->isAlive());
        $this->assertSame(1, $zealot->health);
        $this->assertTrue($zealot->hasBrokenShield());
        $this->assertSame('zombie/zealots/zealot_shield_broken.png', $zealot->getSprite());
    }

    public function test_level_five_wins_with_the_expected_solution(): void
    {
        $state = $this->runLevelFour(
            <<<'CODE'
            hero.moveRight()
            hero.attack("zealot")
            hero.attack("zealot")
            hero.moveRight(3)
            hero.attack("Ganado")
            hero.attack("Ganado")
            hero.moveRight(3)
            CODE
        );

        $this->assertTrue($state->win);
        $this->assertFalse($state->lose);
        $this->assertSame('success', $state->messageType);
        $this->assertCount(0, array_filter($state->zombies, fn ($zombie) => $zombie->isAlive()));
    }

    public function test_level_five_ganado_attacks_accept_case_and_quote_variations(): void
    {
        $state = $this->runLevelFour(
            <<<'CODE'
            Hero.MoveRight()
            HERO.ATTACK('ZEALOT')
            hero.attack("zealot")
            HERO.MOVERIGHT(3)
            hero.attack('ganado')
            Hero.Attack("GANADO")
            hero.moveRight(3)
            CODE
        );

        $this->assertTrue($state->win);
        $this->assertFalse($state->lose);
        $this->assertCount(0, array_filter($state->zombies, fn ($zombie) => $zombie->isAlive()));
    }

    public function test_level_five_single_named_attack_eliminates_ganado(): void
    {
        $state = $this->runLevelFour(
            <<<'CODE'
            hero.moveRight()
            hero.attack("zealot")
            hero.attack("zealot")
            hero.moveRight()
            hero.moveRight()
            hero.moveRight()
            hero.attack("Ganado")
            CODE
        );

        $this->assertFalse($state->lose);
        $this->assertCount(0, array_filter($state->zombies, fn ($zombie) => $zombie->isAlive()));
        $this->assertContains('Ganado eliminado em (5, 4)!', $state->log);
    }

    public function test_level_five_does_not_require_move_up_to_activate_enemies(): void
    {
        $state = $this->runLevelFour(
            <<<'CODE'
            hero.moveRight()
            hero.attack("zealot")
            hero.attack("zealot")
            CODE
        );

        $this->assertFalse($state->win);
        $this->assertFalse($state->lose);
        $this->assertSame(1, $state->player->x);
        $this->assertSame(4, $state->player->y);
        $this->assertSame(['Ganado'], array_values(array_map(
            fn ($zombie) => $zombie->name,
            array_filter($state->zombies, fn ($zombie) => $zombie->isAlive())
        )));
    }

    private function runLevelFour(string $code): GameState
    {
        $state = new GameState();
        $engine = new GameEngine();
        $engine->initLevel($state, 5);

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
