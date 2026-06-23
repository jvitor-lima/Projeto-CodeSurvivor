<?php

namespace App\Game\Commands;

use App\Game\Engine\GameState;

/**
 * Interface base para todos os comandos do jogo.
 *
 * O Command Pattern permite encapsular cada ação do jogador como um objeto independente.
 * Isso facilita:
 * - Enfileirar e executar ações passo a passo (game loop).
 * - Adicionar novos comandos sem alterar o código existente (Open/Closed Principle).
 * - Serializar e desserializar a fila de comandos para o Livewire.
 * - Futuramente: implementar undo/redo de ações.
 */
interface Command
{
    /**
     * Executa a ação do comando sobre o GameState.
     *
     * @param  GameState $state Estado atual do jogo. Será modificado pelo comando.
     * @return void
     */
    public function execute(GameState $state): void;

    /**
     * Retorna o nome legível do comando para exibição no log.
     *
     * @return string Ex: "Mover para direita", "Atacar"
     */
    public function getLabel(): string;

    /**
     * Serializa o comando para um array (para persistência no Livewire).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Reconstrói o comando a partir de um array serializado.
     *
     * @param  array<string, mixed> $data
     * @return static
     */
    public static function fromArray(array $data): static;
}
