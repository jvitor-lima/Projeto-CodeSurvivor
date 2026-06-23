<?php

namespace App\Game\Engine;

use App\Game\Entities\Goal;
use App\Game\Entities\Player;
use App\Game\Entities\Zombie;

/**
 * Estado central do jogo.
 *
 * O GameState é o único objeto de verdade do jogo. Ele contém todas as informações
 * necessárias para renderizar e processar um frame: mapa, entidades, status e logs.
 *
 * É serializado para array e armazenado como propriedade pública do componente Livewire,
 * garantindo que o estado persista entre as requisições.
 *
 * Regra de ouro: nenhum System ou Command altera o estado diretamente sem passar pelo GameState.
 */
class GameState
{
    /**
     * Número da fase atual.
     *
     * @var int
     */
    public int $level;

    /**
     * Tamanho do grid (NxN).
     *
     * @var int
     */
    public int $gridSize;

    /**
     * Mapa de tiles do grid. Indexado por [y][x].
     * Valores possíveis de tile: 'caminho' | 'grama' | 'agua' | 'parede'
     *
     * @var string[][]
     */
    public array $map;

    /**
     * Entidade do jogador.
     *
     * @var Player
     */
    public Player $player;

    /**
     * Entidade do objetivo da fase.
     *
     * @var Goal
     */
    public Goal $goal;

    /**
     * Lista de zumbis presentes na fase.
     *
     * @var Zombie[]
     */
    public array $zombies;

    /**
     * Indica se o game loop está em execução.
     *
     * @var bool
     */
    public bool $isRunning;

    /**
     * Indica se o jogador venceu a fase.
     *
     * @var bool
     */
    public bool $win;

    /**
     * Indica se o jogador perdeu a fase.
     *
     * @var bool
     */
    public bool $lose;

    /**
     * Atraso, em milissegundos, antes de exibir a modal de derrota.
     *
     * @var int
     */
    public int $loseModalDelayMs;

    /**
     * Mensagem de feedback exibida ao jogador (ex: erro, vitória, derrota).
     *
     * @var string
     */
    public string $message;

    /**
     * Tipo da mensagem de feedback: 'success' | 'error' | 'info' | ''
     *
     * @var string
     */
    public string $messageType;

    /**
     * Log de eventos do jogo para o painel de saída (console do jogo).
     *
     * @var string[]
     */
    public array $log;

    /**
     * Tipo da fase atual: 'objetivo' | 'stealth' | 'combate'
     *
     * @var string
     */
    public string $phaseType;

    /**
     * Objetos decorativos e de cenario no mapa.
     * Cada item: ['x' => int, 'y' => int, 'type' => string]
     *
     * @var array<array<string, mixed>>
     */
    public array $scenery;

    /**
     * Itens coletaveis ainda presentes na fase.
     *
     * @var array<array<string, mixed>>
     */
    public array $collectibles;

    /**
     * Itens que precisam estar coletados para concluir a fase.
     *
     * @var string[]
     */
    public array $requiredItems;

    /**
     * Identificador da atmosfera visual da fase.
     *
     * @var string
     */
    public string $atmosphere;

    /**
     * Descrição textual do objetivo da fase.
     *
     * @var string
     */
    public string $objective;

    /**
     * Codigo inicial exibido no editor para a fase atual.
     *
     * @var string
     */
    public string $initialCode;

    /**
     * Indica se a tela deve tremer (efeito de impacto).
     *
     * @var bool
     */
    public bool $shake;

    /**
     * Cria um novo GameState com valores padrão.
     */
    public function __construct()
    {
        $this->level       = 1;
        $this->gridSize    = 8;
        $this->map         = [];
        $this->player      = new Player();
        $this->goal        = new Goal();
        $this->zombies     = [];
        $this->isRunning   = false;
        $this->win         = false;
        $this->lose        = false;
        $this->loseModalDelayMs = 0;
        $this->message     = '';
        $this->messageType = '';
        $this->log         = [];
        $this->phaseType   = 'objetivo';
        $this->scenery     = [];
        $this->collectibles = [];
        $this->requiredItems = [];
        $this->atmosphere  = 'default';
        $this->objective   = '';
        $this->initialCode = '';
        $this->shake       = false;
    }

    /**
     * Adiciona uma entrada ao log de eventos do jogo.
     *
     * @param string $entry Texto do evento (ex: "Herói moveu para direita").
     * @return void
     */
    public function addLog(string $entry): void
    {
        $this->log[] = $entry;

        // Mantém apenas as últimas 20 entradas para não sobrecarregar a UI.
        if (count($this->log) > 20) {
            $this->log = array_slice($this->log, -20);
        }
    }

    /**
     * Define uma mensagem de feedback para o jogador.
     *
     * @param string $message Texto da mensagem.
     * @param string $type    Tipo: 'success' | 'error' | 'info'
     * @return void
     */
    public function setMessage(string $message, string $type = 'info'): void
    {
        $this->message     = $message;
        $this->messageType = $type;
    }

    /**
     * Limpa a mensagem de feedback atual.
     *
     * @return void
     */
    public function clearMessage(): void
    {
        $this->message     = '';
        $this->messageType = '';
    }

    /**
     * Serializa o GameState para um array associativo (para o Livewire).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'level'       => $this->level,
            'gridSize'    => $this->gridSize,
            'map'         => $this->map,
            'player'      => $this->player->toArray(),
            'goal'        => $this->goal->toArray(),
            'zombies'     => array_map(fn(Zombie $z) => $z->toArray(), $this->zombies),
            'isRunning'   => $this->isRunning,
            'win'         => $this->win,
            'lose'        => $this->lose,
            'loseModalDelayMs' => $this->loseModalDelayMs,
            'message'     => $this->message,
            'messageType' => $this->messageType,
            'log'         => $this->log,
            'phaseType'   => $this->phaseType,
            'scenery'     => $this->scenery,
            'collectibles' => $this->collectibles,
            'requiredItems' => $this->requiredItems,
            'atmosphere'  => $this->atmosphere,
            'objective'   => $this->objective,
            'initialCode' => $this->initialCode,
            'shake'       => $this->shake,
        ];
    }

    /**
     * Reconstrói o GameState a partir de um array serializado.
     *
     * @param  array<string, mixed> $data Array serializado pelo toArray().
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $state = new static();

        $state->level       = $data['level']    ?? 1;
        $state->gridSize    = $data['gridSize']  ?? 8;
        $state->map         = $data['map']       ?? [];
        $state->player      = Player::fromArray($data['player'] ?? []);
        $state->goal        = Goal::fromArray($data['goal']     ?? []);
        $state->isRunning   = $data['isRunning'] ?? false;
        $state->win         = $data['win']       ?? false;
        $state->lose        = $data['lose']      ?? false;
        $state->loseModalDelayMs = $data['loseModalDelayMs'] ?? 0;
        $state->message     = $data['message']   ?? '';
        $state->messageType = $data['messageType'] ?? '';
        $state->log         = $data['log']       ?? [];
        $state->phaseType   = $data['phaseType'] ?? 'objetivo';
        $state->scenery     = $data['scenery']   ?? [];
        $state->collectibles = $data['collectibles'] ?? [];
        $state->requiredItems = $data['requiredItems'] ?? [];
        $state->atmosphere  = $data['atmosphere'] ?? 'default';
        $state->objective   = $data['objective']  ?? '';
        $state->initialCode = $data['initialCode'] ?? '';
        $state->shake       = $data['shake']      ?? false;

        $state->zombies = array_map(
            fn(array $z) => Zombie::fromArray($z),
            $data['zombies'] ?? []
        );

        return $state;
    }
}
