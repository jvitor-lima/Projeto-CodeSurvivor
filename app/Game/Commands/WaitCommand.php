<?php

namespace App\Game\Commands;

use App\Game\Engine\GameState;

/**
 * Comando de espera: consome turnos sem mover Leon.
 *
 * Corresponde a hero.wait() ou hero.wait(N) no editor.
 */
class WaitCommand implements Command
{
    private int $sourceLine;

    public function __construct(int $sourceLine = 0)
    {
        $this->sourceLine = $sourceLine;
    }

    public function execute(GameState $state): void
    {
        $state->addLog('Leon esperou e observou o movimento dos inimigos.');
    }

    public function getLabel(): string
    {
        return 'Esperar';
    }

    public function toArray(): array
    {
        return [
            'type' => 'wait',
            'sourceLine' => $this->sourceLine,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            sourceLine: $data['sourceLine'] ?? 0,
        );
    }
}
