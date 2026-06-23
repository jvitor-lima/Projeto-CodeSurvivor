<?php

namespace Tests\Unit;

use App\Game\Config\LevelConfig;
use App\Game\Engine\GameEngine;
use App\Game\Engine\GameState;
use PHPUnit\Framework\TestCase;

class LevelSixLoopCorridorTest extends TestCase
{
    public function test_level_six_is_registered_as_loop_corridor(): void
    {
        $levels = LevelConfig::getAllLevels();
        $config = LevelConfig::getLevelBySequence(6);

        $this->assertArrayHasKey(6, $levels);
        $this->assertSame('Corredor de Repeticao', $levels[6]['name']);
        $this->assertSame(10, $config['gridSize']);
        $this->assertSame('objetivo', $config['phaseType']);
        $this->assertSame(['x' => 0, 'y' => 3], array_intersect_key($config['player'], ['x' => true, 'y' => true]));
        $this->assertSame('right', $config['player']['direction']);
        $this->assertSame(['x' => 8, 'y' => 3], $config['goal']);
        $this->assertSame([], $config['zombies']);
    }

    public function test_level_six_uses_exact_wave_path_coordinates(): void
    {
        $config = LevelConfig::getLevelBySequence(6);
        $path = [];

        foreach ($config['map'] as $y => $row) {
            foreach ($row as $x => $tile) {
                if ($tile === 'caminho') {
                    $path[] = [$x, $y];
                }
            }
        }

        $this->assertSame([
            [2, 1], [3, 1], [4, 1], [6, 1], [7, 1], [8, 1],
            [2, 2], [4, 2], [6, 2], [8, 2],
            [0, 3], [1, 3], [2, 3], [4, 3], [5, 3], [6, 3], [8, 3],
        ], $path);
    }

    public function test_level_six_wins_with_documented_solution(): void
    {
        $state = $this->runLevelSix(
            <<<'CODE'
            hero.moveRight()
            hero.moveRight()
            hero.moveUp()
            hero.moveUp()
            hero.moveRight()
            hero.moveRight()
            hero.moveDown()
            hero.moveDown()
            hero.moveRight()
            hero.moveRight()
            hero.moveUp()
            hero.moveUp()
            hero.moveRight()
            hero.moveRight()
            hero.moveDown()
            hero.moveDown()
            CODE
        );

        $this->assertTrue($state->win);
        $this->assertFalse($state->lose);
        $this->assertSame('success', $state->messageType);
        $this->assertSame(8, $state->player->x);
        $this->assertSame(3, $state->player->y);
    }

    public function test_level_six_initial_repeat_code_reaches_goal(): void
    {
        $config = LevelConfig::getLevelBySequence(6);

        $this->assertSame(
            <<<'CODE'
repeat(2) {
  hero.moveRight(2)
  hero.moveUp(2)
  hero.moveRight(2)
  hero.moveDown(2)
}
CODE,
            $config['initialCode']
        );

        $state = $this->runLevelSix($config['initialCode']);

        $this->assertTrue($state->win);
        $this->assertSame(8, $state->player->x);
        $this->assertSame(3, $state->player->y);
    }

    public function test_level_six_blocks_direct_wall_movement(): void
    {
        $state = $this->runLevelSix('hero.moveUp()');

        $this->assertFalse($state->win);
        $this->assertSame(0, $state->player->x);
        $this->assertSame(3, $state->player->y);
        $this->assertStringContainsString('Movimento bloqueado: (0, 2)', implode("\n", $state->log));
    }

    private function runLevelSix(string $code): GameState
    {
        $state = new GameState();
        $engine = new GameEngine();
        $engine->initLevel($state, 6);

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
