<?php

namespace Tests\Unit;

use App\Game\Engine\GameEngine;
use App\Game\Engine\GameState;
use App\Game\Services\RouteFeedbackService;
use PHPUnit\Framework\TestCase;

class RouteFeedbackServiceTest extends TestCase
{
    public function test_blocked_route_receives_short_guidance(): void
    {
        [$state, $context] = $this->runLevelOne('hero.moveLeft()');

        $analysis = (new RouteFeedbackService())->analyzeAttempt($state, $context);

        $this->assertSame('blocked', $analysis['attempt']['lastErrorType']);
        $this->assertSame('Rota bloqueada', $analysis['feedback']['category']);
        $this->assertStringContainsString('bloqueado', $analysis['feedback']['message']);
    }

    public function test_incomplete_route_reports_progress(): void
    {
        [$state, $context] = $this->runLevelOne('hero.moveDown(2)');

        $analysis = (new RouteFeedbackService())->analyzeAttempt($state, $context);

        $this->assertSame('incomplete', $analysis['attempt']['lastErrorType']);
        $this->assertSame('Dica de rota', $analysis['feedback']['category']);
        $this->assertLessThan($context['startDistance'], $analysis['attempt']['lastDistance']);
    }

    public function test_success_route_reports_objective_reached(): void
    {
        [$state, $context] = $this->runLevelOne(
            <<<'CODE'
hero.moveDown(2)
hero.moveRight(2)
hero.moveDown(5)
CODE
        );

        $analysis = (new RouteFeedbackService())->analyzeAttempt($state, $context);

        $this->assertSame('success', $analysis['attempt']['lastErrorType']);
        $this->assertSame('Progresso', $analysis['feedback']['category']);
        $this->assertSame(0, $analysis['attempt']['lastDistance']);
    }

    /**
     * @return array{GameState, array<string, mixed>}
     */
    private function runLevelOne(string $code): array
    {
        $state = new GameState();
        $engine = new GameEngine();
        $engine->initLevel($state, 1);

        $context = [
            'code' => $code,
            'level' => 1,
            'attemptNumber' => 1,
            'startPosition' => ['x' => $state->player->x, 'y' => $state->player->y],
            'startDistance' => (new RouteFeedbackService())->distance(
                ['x' => $state->player->x, 'y' => $state->player->y],
                ['x' => $state->goal->x, 'y' => $state->goal->y],
            ),
            'previousDistance' => null,
        ];

        $queue = $engine->prepareCommands($state, $code);
        $step = 0;
        $done = false;

        for ($guard = 0; ! $done && $guard < 100; $guard++) {
            $result = $engine->step($state, $queue, $step);
            $queue = $result['queue'];
            $step = $result['step'];
            $done = $result['done'];
        }

        $this->assertTrue($done);

        return [$state, $context];
    }
}
