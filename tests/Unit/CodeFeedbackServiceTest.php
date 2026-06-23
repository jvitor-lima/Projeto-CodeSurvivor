<?php

namespace Tests\Unit;

use App\Game\Services\CodeFeedbackService;
use PHPUnit\Framework\TestCase;

class CodeFeedbackServiceTest extends TestCase
{
    public function test_empty_code_receives_beginner_guidance(): void
    {
        $feedback = (new CodeFeedbackService())->analyze('');

        $this->assertSame('info', $feedback[0]['type']);
        $this->assertStringContainsString('Digite pelo menos um comando', $feedback[0]['message']);
    }

    public function test_repeated_moves_suggest_parameterized_command(): void
    {
        $feedback = (new CodeFeedbackService())->analyze(
            <<<'CODE'
hero.moveDown()
hero.moveDown()
hero.moveRight()
hero.moveRight()
hero.moveRight()
CODE
        );

        $messages = implode("\n", array_column($feedback, 'message'));

        $this->assertStringContainsString('hero.moveDown(2)', $messages);
        $this->assertStringContainsString('hero.moveRight(3)', $messages);
    }

    public function test_inverted_command_order_receives_friendly_explanation(): void
    {
        $feedback = (new CodeFeedbackService())->analyze('move.heroDown()');

        $this->assertSame('warning', $feedback[0]['type']);
        $this->assertStringContainsString('ordem do comando esta invertida', $feedback[0]['message']);
        $this->assertStringContainsString('hero.moveDown()', $feedback[0]['message']);
    }
}
