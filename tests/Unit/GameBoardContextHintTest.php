<?php

namespace Tests\Unit;

use App\Livewire\GameBoard;
use PHPUnit\Framework\TestCase;

class GameBoardContextHintTest extends TestCase
{
    public function test_context_hint_does_not_stack_same_tip_on_repeated_clicks(): void
    {
        $component = new GameBoard();
        $component->gameState = [
            'level' => 1,
            'objective' => 'Leve o sobrevivente ate o ponto seguro.',
        ];
        $component->attemptTracker = [
            'count' => 0,
            'lastErrorType' => null,
            'repeatedSameError' => false,
        ];

        $component->askForContextHint();
        $component->askForContextHint();
        $component->askForContextHint();

        $this->assertCount(1, $component->codeFeedback);
        $this->assertSame('hint', $component->codeFeedback[0]['source']);
        $this->assertSame('Estou preso', $component->codeFeedback[0]['title']);
    }
}
