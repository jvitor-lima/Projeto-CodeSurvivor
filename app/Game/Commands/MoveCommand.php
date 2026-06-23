<?php

namespace App\Game\Commands;

use App\Game\Engine\GameState;
use App\Game\Systems\MovementSystem;

/**
 * Comando que move o herói em uma direção.
 *
 * Corresponde às chamadas: hero.moveRight(), hero.moveLeft(),
 * hero.moveUp(), hero.moveDown() no editor de código.
 *
 * Delega a lógica de validação e execução ao MovementSystem.
 */
class MoveCommand implements Command
{
    /**
     * Direção do movimento.
     * Valores possíveis: 'up' | 'down' | 'left' | 'right'
     *
     * @var string
     */
    private string $direction;

    /**
     * Quantidade de passos a mover.
     *
     * @var int
     */
    private int $steps;

    /**
     * Número da linha no código-fonte do jogador (para feedback de erro).
     *
     * @var int
     */
    private int $sourceLine;

    /**
     * Cria um novo MoveCommand.
     *
     * @param string $direction  Direção do movimento.
     * @param int    $steps      Quantidade de passos.
     * @param int    $sourceLine Linha do código que gerou este comando.
     */
    public function __construct(string $direction, int $steps = 1, int $sourceLine = 0)
    {
        $this->direction  = $direction;
        $this->steps      = max(1, $steps);
        $this->sourceLine = $sourceLine;
    }

    /**
     * {@inheritdoc}
     *
     * Executa um único movimento (1 célula por vez).
     * A expansão de múltiplos passos é feita pelo CommandParserService.
     */
    public function execute(GameState $state): void
    {
        $movementSystem = new MovementSystem();
        $movementSystem->movePlayer($state, $this->direction);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        $labels = [
            'up'    => 'Mover para cima',
            'down'  => 'Mover para baixo',
            'left'  => 'Mover para esquerda',
            'right' => 'Mover para direita',
        ];

        $label = $labels[$this->direction] ?? "Mover ({$this->direction})";
        
        return $this->steps > 1 ? "{$label} ({$this->steps}x)" : $label;
    }

    /**
     * Retorna a direção deste comando.
     *
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * Retorna a linha do código-fonte que gerou este comando.
     *
     * @return int
     */
    public function getSourceLine(): int
    {
        return $this->sourceLine;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'type'       => 'move',
            'direction'  => $this->direction,
            'steps'      => $this->steps,
            'sourceLine' => $this->sourceLine,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data): static
    {
        return new static(
            direction:  $data['direction']  ?? 'down',
            steps:      $data['steps']      ?? 1,
            sourceLine: $data['sourceLine'] ?? 0,
        );
    }
}
