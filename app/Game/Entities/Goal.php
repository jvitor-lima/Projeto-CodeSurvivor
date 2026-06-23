<?php

namespace App\Game\Entities;

/**
 * Entidade que representa o objetivo (destino) de uma fase.
 *
 * O Goal é o ponto que o jogador deve alcançar para completar a fase.
 * Não possui lógica própria — é verificado pelo GameEngine após cada movimento.
 */
class Goal extends Entity
{
    /**
     * Cria um novo Goal.
     *
     * @param int    $x  Posição X no grid.
     * @param int    $y  Posição Y no grid.
     * @param string $id Identificador único.
     */
    /**
     * @var string|null Asset visual customizado para o objetivo.
     */
    public ?string $asset = null;

    public function __construct(int $x = 0, int $y = 0, string $id = 'goal')
    {
        parent::__construct($x, $y, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'x'     => $this->x,
            'y'     => $this->y,
            'asset' => $this->asset,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data): static
    {
        $goal = new static(
            x:  $data['x'] ?? 0,
            y:  $data['y'] ?? 0,
            id: $data['id'] ?? 'goal',
        );
        $goal->asset = $data['asset'] ?? null;
        return $goal;
    }
}
