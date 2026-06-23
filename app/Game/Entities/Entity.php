<?php

namespace App\Game\Entities;

/**
 * Classe base abstrata para todas as entidades do jogo.
 *
 * Uma entidade é qualquer objeto que ocupa uma posição no mapa (Player, Zombie, Goal).
 * Ela carrega apenas dados de posição e identificação — a lógica fica nos Systems.
 */
abstract class Entity
{
    /**
     * Identificador único da entidade.
     *
     * @var string
     */
    public string $id;

    /**
     * Posição horizontal da entidade no grid (coluna).
     *
     * @var int
     */
    public int $x;

    /**
     * Posição vertical da entidade no grid (linha).
     *
     * @var int
     */
    public int $y;

    /**
     * Cria uma nova entidade com posição inicial.
     *
     * @param int    $x  Posição X (coluna) no grid.
     * @param int    $y  Posição Y (linha) no grid.
     * @param string $id Identificador único. Se vazio, gera um UUID simples.
     */
    public function __construct(int $x, int $y, string $id = '')
    {
        $this->x  = $x;
        $this->y  = $y;
        $this->id = $id !== '' ? $id : uniqid(static::class . '_', true);
    }

    /**
     * Serializa a entidade para um array associativo (para persistência no Livewire).
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    /**
     * Reconstrói a entidade a partir de um array (desserialização do Livewire).
     *
     * @param  array<string, mixed> $data
     * @return static
     */
    abstract public static function fromArray(array $data): static;
}
