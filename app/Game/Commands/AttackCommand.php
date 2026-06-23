<?php

namespace App\Game\Commands;

use App\Game\Engine\GameState;
use App\Game\Systems\CombatSystem;

/**
 * Comando que faz o herói atacar na direção em que está olhando.
 *
 * Corresponde à chamada: hero.attack() no editor de código.
 *
 * Delega a lógica de resolução de combate ao CombatSystem.
 */
class AttackCommand implements Command
{
    /**
     * Número da linha no código-fonte do jogador (para feedback de erro).
     *
     * @var int
     */
    private int $sourceLine;
    private ?string $targetName;

    /**
     * Cria um novo AttackCommand.
     *
     * @param int $sourceLine Linha do código que gerou este comando.
     */
    public function __construct(int $sourceLine = 0, ?string $targetName = null)
    {
        $this->sourceLine = $sourceLine;
        $this->targetName = $targetName;
    }

    /**
     * {@inheritdoc}
     *
     * Instancia o CombatSystem e executa o ataque do jogador.
     */
    public function execute(GameState $state): void
    {
        $combatSystem = new CombatSystem();
        $combatSystem->playerAttack($state, $this->targetName);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return $this->targetName ? "Atacar {$this->targetName}" : 'Atacar';
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
            'type'       => 'attack',
            'sourceLine' => $this->sourceLine,
            'targetName' => $this->targetName,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data): static
    {
        return new static(
            sourceLine: $data['sourceLine'] ?? 0,
            targetName: $data['targetName'] ?? null,
        );
    }
}
