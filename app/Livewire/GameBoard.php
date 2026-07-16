<?php

namespace App\Livewire;

use App\Game\Config\TileVisualConfig;
use App\Game\Config\LevelConfig;
use App\Game\Config\LevelTutorialConfig;
use App\Game\Config\LevelVisualConfig;
use App\Game\Engine\GameEngine;
use App\Game\Engine\GameState;
use App\Game\Entities\Zombie;
use App\Game\Services\CodeFeedbackService;
use App\Game\Services\InventoryService;
use App\Game\Services\LoreService;
use App\Game\Services\ProgressService;
use App\Game\Services\RouteFeedbackService;
use App\Game\Systems\MovementSystem;
use Livewire\Component;

/**
 * Componente Livewire principal do jogo.
 *
 * O GameBoard é a ponte entre a interface do usuário (Blade) e a lógica do jogo (GameEngine).
 * Ele é responsável por:
 * - Manter o estado do jogo serializado como propriedades Livewire (para persistência entre requests).
 * - Receber eventos da UI (clique em botões, drag-and-drop de comandos).
 * - Delegar toda a lógica de jogo ao GameEngine.
 * - Fornecer dados computados para a view (sprites, tiles, barra de vida).
 *
 * Regra: nenhuma lógica de jogo deve existir aqui. Apenas orquestração de UI.
 */
class GameBoard extends Component
{
    private const INVENTORY_SLOT_COUNT = 8;

    // =========================================================================
    // ESTADO SERIALIZADO (propriedades Livewire)
    // =========================================================================

    /**
     * Estado do jogo serializado como array para persistência no Livewire.
     * Desserializado para GameState antes de cada operação.
     *
     * @var array<string, mixed>
     */
    public array $gameState = [];

    /**
     * Código digitado pelo jogador no editor.
     *
     * @var string
     */
    public string $commands = '';

    /**
     * Fila de comandos serializada aguardando execução.
     * Cada item é um array com 'type', 'direction'/'sourceLine', etc.
     *
     * @var array<array<string, mixed>>
     */
    public array $commandQueue = [];

    /**
     * Índice do comando atual na fila (game loop step).
     *
     * @var int
     */
    public int $currentStep = 0;

    /**
     * Estado visual da animacao do heroi. Nao interfere nas regras do jogo.
     *
     * @var bool
     */
    public bool $playerIsWalking = false;

    /**
     * Incrementa a cada passo andado para reiniciar a animacao CSS no Livewire.
     *
     * @var int
     */
    public int $playerAnimationTick = 0;

    /**
     * Indica se o painel de referência de comandos está visível.
     *
     * @var bool
     */
    public bool $showReference = true;

    /**
     * Controla a modal de inventario e permite abrir automaticamente apos coleta.
     *
     * @var bool
     */
    public bool $showInventoryModal = false;

    /**
     * Dicas educativas geradas a partir do codigo digitado pelo jogador.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $codeFeedback = [];

    /**
     * Registro simples das tentativas da fase atual, sem persistencia em banco.
     *
     * @var array<string, mixed>
     */
    public array $attemptTracker = [];

    /**
     * Dados da tentativa em execucao usados para gerar feedback de rota.
     *
     * @var array<string, mixed>
     */
    public array $attemptContext = [];

    // =========================================================================
    // INICIALIZAÇÃO
    // =========================================================================

    /**
     * Inicializa o componente carregando a fase solicitada (ou 1 por padrão).
     *
     * @return void
     */
    public function mount(): void
    {
        // Obtém o nível da query string ou usa 1 como padrão
        $level = request()->query('level', 1);
        
        // Valida se o nível está desbloqueado
        $progressService = new ProgressService();
        if (!$progressService->isLevelUnlocked($level)) {
            $level = 1; // Fallback para fase 1 se tentar acessar fase bloqueada
        }
        
        $state  = new GameState();
        (new InventoryService())->syncPlayer($state->player);
        $engine = new GameEngine();
        $engine->initLevel($state, $level);
        $this->gameState = $state->toArray();
        $this->commands = $state->initialCode;
        $this->codeFeedback = [];
        $this->attemptTracker = $this->freshAttemptTracker();
        $this->attemptContext = [];
    }

    // =========================================================================
    // AÇÕES DO JOGADOR (chamadas pela view via wire:click)
    // =========================================================================

    /**
     * Analisa o código do editor e inicia a execução dos comandos.
     *
     * Chamado quando o jogador clica em "Rodar".
     *
     * @return void
     */
    public function runCommands(): void
    {
        // 1. Cria um estado novo para a fase atual (Reset Total)
        $state  = new GameState();
        (new InventoryService())->syncPlayer($state->player);
        $engine = new GameEngine();
        
        // Mantém o nível atual, mas reseta o resto
        $currentLevel = $this->gameState['level'] ?? 1;
        $engine->initLevel($state, $currentLevel);

        $this->codeFeedback = (new CodeFeedbackService())->analyze($this->commands);

        // 2. Prepara a fila a partir do código
        $this->commandQueue = $engine->prepareCommands($state, $this->commands);

        if (! empty($this->codeFeedback) && ! empty($state->message) && $state->messageType === 'error') {
            $firstWarning = collect($this->codeFeedback)->first(fn (array $item) => ($item['type'] ?? '') === 'warning');

            if ($firstWarning) {
                $state->setMessage($firstWarning['message'], 'error');
            }
        }

        $this->attemptContext = [];

        if (! empty($this->commandQueue)) {
            $this->attemptContext = [
                'code' => $this->commands,
                'level' => $currentLevel,
                'attemptNumber' => ((int) ($this->attemptTracker['count'] ?? 0)) + 1,
                'startPosition' => [
                    'x' => $state->player->x,
                    'y' => $state->player->y,
                ],
                'startDistance' => (new RouteFeedbackService())->distance(
                    ['x' => $state->player->x, 'y' => $state->player->y],
                    ['x' => $state->goal->x, 'y' => $state->goal->y],
                ),
                'previousDistance' => $this->attemptTracker['lastDistance'] ?? null,
                'completed' => false,
            ];
        }

        $this->currentStep  = 0;
        $this->playerIsWalking = false;
        $this->playerAnimationTick = 0;
        $this->showInventoryModal = false;
        
        // 3. Salva o estado resetado (com isRunning=true se o parser deu OK)
        $this->gameState = $state->toArray();
    }

    /**
     * Executa um único passo do game loop.
     *
     * Chamado pelo wire:poll a cada intervalo de tempo.
     * Avança um comando na fila e verifica o estado do jogo.
     *
     * @return void
     */
    public function step(): void
    {
        $commandData = $this->commandQueue[$this->currentStep] ?? null;
        $previousPlayer = $this->gameState['player'] ?? [];
        $previousInventory = $previousPlayer['inventory'] ?? [];

        $state  = GameState::fromArray($this->gameState);
        $engine = new GameEngine();

        $result = $engine->step($state, $this->commandQueue, $this->currentStep);

        $playerMoved = ($commandData['type'] ?? null) === 'move'
            && (
                ($previousPlayer['x'] ?? null) !== $state->player->x
                || ($previousPlayer['y'] ?? null) !== $state->player->y
            );

        $this->playerIsWalking = $playerMoved;
        if ($playerMoved) {
            $this->playerAnimationTick++;
        }

        if ($this->hasNewInventoryItem($previousInventory, $state->player->inventory)) {
            $this->showInventoryModal = true;
        }

        if (($result['done'] ?? false) && ! ($this->attemptContext['completed'] ?? false)) {
            $this->finalizeAttempt($state);
        }

        $this->commandQueue = $result['queue'];
        $this->currentStep  = $result['step'];
        (new InventoryService())->persistPlayer($state->player);
        $this->gameState    = $state->toArray();
    }

    /**
     * Reseta a fase atual para o estado inicial.
     *
     * Chamado quando o jogador clica em "Reset".
     *
     * @return void
     */
    public function resetLevel(): void
    {
        $state  = GameState::fromArray($this->gameState);
        (new InventoryService())->syncPlayer($state->player);
        $engine = new GameEngine();

        $engine->resetLevel($state);

        $this->commandQueue = [];
        $this->currentStep  = 0;
        $this->playerIsWalking = false;
        $this->playerAnimationTick = 0;
        $this->showInventoryModal = false;
        $this->codeFeedback = [];
        $this->attemptContext = [];
        $this->commands     = $state->initialCode;
        $this->gameState    = $state->toArray();
    }

    /**
     * Carrega uma fase específica pelo número.
     *
     * @param  int $level Número da fase.
     * @return void
     */
    public function loadLevel(int $level): void
    {
        $progressService = new ProgressService();
        if (! $progressService->isLevelUnlocked($level)) {
            $state = GameState::fromArray($this->gameState);
            $state->setMessage('Esta fase ainda esta bloqueada no mapa de progresso.', 'error');
            $this->gameState = $state->toArray();
            return;
        }

        $state  = new GameState();
        (new InventoryService())->syncPlayer($state->player);
        $engine = new GameEngine();

        $engine->initLevel($state, $level);

        $this->commandQueue = [];
        $this->currentStep  = 0;
        $this->playerIsWalking = false;
        $this->playerAnimationTick = 0;
        $this->showInventoryModal = false;
        $this->codeFeedback = [];
        $this->attemptTracker = $this->freshAttemptTracker();
        $this->attemptContext = [];
        $this->commands     = $state->initialCode;
        $this->gameState    = $state->toArray();
        $this->dispatchTutorialChanged();
    }

    /**
     * Avança para a próxima fase após a vitória.
     *
     * @return void
     */
    public function nextLevel()
    {
        $state  = GameState::fromArray($this->gameState);
        $engine = new GameEngine();

        // Marca a fase atual como concluída e desbloqueia a próxima
        $currentLevel = $state->level;
        $progressService = new ProgressService();
        $progressService->completeLevel($currentLevel);
        (new InventoryService())->persistPlayer($state->player);

        $nextLevel = $currentLevel + 1;
        $levels = LevelConfig::getAllLevels();

        if (! isset($levels[$nextLevel])) {
            return redirect('/map?completed=1');
        }

        if (isset($levels[$nextLevel]) && LevelConfig::getMapForLevel($nextLevel) !== LevelConfig::getMapForLevel($currentLevel)) {
            return redirect('/map?map=' . LevelConfig::getMapForLevel($nextLevel));
        }

        $engine->nextLevel($state);

        $this->commandQueue = [];
        $this->currentStep  = 0;
        $this->playerIsWalking = false;
        $this->playerAnimationTick = 0;
        $this->showInventoryModal = false;
        $this->codeFeedback = [];
        $this->attemptTracker = $this->freshAttemptTracker();
        $this->attemptContext = [];
        $this->commands     = $state->initialCode;
        $this->gameState    = $state->toArray();
        $this->dispatchTutorialChanged();
    }

    /**
     * Insere um snippet de comando no editor ao clicar em um botão de referência.
     *
     * @param  string $snippet Texto do comando a inserir (ex: "hero.moveRight()").
     * @return void
     */
    public function insertCommand(string $snippet): void
    {
        $snippet = preg_replace(
            '/hero\.move(Right|Left|Up|Down)\(\s*\d+\s*\)/',
            'hero.move$1()',
            $snippet
        ) ?? $snippet;

        $this->commands = rtrim($this->commands) . "\n" . $snippet;
        $this->codeFeedback = [];
    }

    public function updatedCommands(): void
    {
        $this->codeFeedback = [];
    }

    /**
     * Alterna a visibilidade do painel de referência de comandos.
     *
     * @return void
     */
    public function toggleReference(): void
    {
        $this->showReference = ! $this->showReference;
    }

    public function askForContextHint(): void
    {
        $hint = (new RouteFeedbackService())->stuckHint(
            (int) ($this->gameState['level'] ?? 1),
            $this->attemptTracker,
            $this->gameState,
        );

        $this->codeFeedback = $this->prependUniqueFeedback($this->codeFeedback, $hint, 'hint');
        $this->dispatch('codesurvivor-context-hint-added');
    }

    public function moveInventoryItem(int $fromSlot, int $toSlot): void
    {
        if ($fromSlot === $toSlot) {
            return;
        }

        $slotCount = $this->getInventorySlotCount();
        if ($fromSlot < 0 || $toSlot < 0 || $fromSlot >= $slotCount || $toSlot >= $slotCount) {
            return;
        }

        $slots = $this->getInventorySlots();
        if (empty($slots[$fromSlot])) {
            return;
        }

        [$slots[$fromSlot], $slots[$toSlot]] = [$slots[$toSlot], $slots[$fromSlot]];

        $inventory = $this->gameState['player']['inventory'] ?? [];
        if (! is_array($inventory)) {
            return;
        }

        foreach ($slots as $index => $slot) {
            if (empty($slot['id']) || ! isset($inventory[$slot['id']])) {
                continue;
            }

            $inventory[$slot['id']]['slot'] = $index;
        }

        $this->gameState['player']['inventory'] = $inventory;
        (new InventoryService())->saveInventory($inventory);
    }

    // =========================================================================
    // DADOS COMPUTADOS (para a view)
    // =========================================================================

    /**
     * Retorna o caminho do asset de tile para um tipo de tile.
     *
     * @param  string $type Tipo do tile.
     * @return string
     */
    public function getTileData(string $type, int $x, int $y): array
    {
        return [
            'asset' => TileVisualConfig::assetFor(
                $type,
                $this->gameState['map'] ?? [],
                $x,
                $y,
                $this->gameState['atmosphere'] ?? 'default',
            ),
            'rotate' => 0
        ];
    }

    public function getTileClasses(string $type): string
    {
        return TileVisualConfig::tileClasses($type, $this->gameState['atmosphere'] ?? 'default');
    }

    public function getPathConnectionClasses(int $x, int $y): string
    {
        $map = $this->gameState['map'] ?? [];

        if (($map[$y][$x] ?? null) !== 'caminho') {
            return '';
        }

        $connections = [];

        if (($map[$y - 1][$x] ?? null) === 'caminho') {
            $connections[] = 'path-up';
        }

        if (($map[$y + 1][$x] ?? null) === 'caminho') {
            $connections[] = 'path-down';
        }

        if (($map[$y][$x - 1] ?? null) === 'caminho') {
            $connections[] = 'path-left';
        }

        if (($map[$y][$x + 1] ?? null) === 'caminho') {
            $connections[] = 'path-right';
        }

        $player = $this->gameState['player'] ?? [];
        $goal = $this->gameState['goal'] ?? [];

        if (($player['x'] ?? null) === $x && ($player['y'] ?? null) === $y) {
            $connections[] = 'path-start';
        }

        if (($goal['x'] ?? null) === $x && ($goal['y'] ?? null) === $y) {
            $connections[] = 'path-goal';
        }

        $connections[] = 'path-guide';

        if (($this->gameState['level'] ?? 1) === 1) {
            $connections[] = 'path-tutorial';
        }

        return implode(' ', $connections);
    }

    public function getObjectiveAsset(): string
    {
        return TileVisualConfig::objectiveAsset();
    }

    public function getBoardBackgroundAsset(): string
    {
        return $this->getLevelVisual()['background'];
    }

    public function getLevelVisual(): array
    {
        return LevelVisualConfig::forLevel(
            $this->gameState['level'] ?? 1,
            $this->gameState['gridSize'] ?? 8,
        );
    }

    public function getLevelNumbers(): array
    {
        $currentMap = LevelConfig::getMapForLevel((int) ($this->gameState['level'] ?? 1));

        return array_keys(LevelConfig::getLevelsForMap($currentMap));
    }

    public function shouldReturnToMapAfterWin(): bool
    {
        if (! ($this->gameState['win'] ?? false)) {
            return false;
        }

        $currentLevel = (int) ($this->gameState['level'] ?? 1);
        $nextLevel = $currentLevel + 1;
        $levels = LevelConfig::getAllLevels();

        return isset($levels[$nextLevel])
            && LevelConfig::getMapForLevel($nextLevel) !== LevelConfig::getMapForLevel($currentLevel);
    }

    public function getNextLevelActionLabel(): string
    {
        return $this->shouldReturnToMapAfterWin() ? 'Abrir Novo Mapa' : 'Proxima Fase';
    }

    public function getNextLevelActionShortLabel(): string
    {
        return $this->shouldReturnToMapAfterWin() ? 'MAPA' : 'PROXIMA';
    }

    public function getInventoryItems(): array
    {
        return array_values($this->gameState['player']['inventory'] ?? []);
    }

    public function getInventorySlotCount(): int
    {
        $inventory = $this->gameState['player']['inventory'] ?? [];

        return max(self::INVENTORY_SLOT_COUNT, is_array($inventory) ? count($inventory) : 0);
    }

    public function getInventorySlots(): array
    {
        $inventory = $this->gameState['player']['inventory'] ?? [];
        $slotCount = $this->getInventorySlotCount();
        $slots = array_fill(0, $slotCount, null);

        if (! is_array($inventory)) {
            return $slots;
        }

        foreach ($inventory as $key => $item) {
            if (! is_array($item)) {
                continue;
            }

            $item['id'] = (string) ($item['id'] ?? $key);
            $preferredSlot = filter_var($item['slot'] ?? null, FILTER_VALIDATE_INT);

            if (
                $preferredSlot !== false
                && $preferredSlot >= 0
                && $preferredSlot < $slotCount
                && $slots[$preferredSlot] === null
            ) {
                $slots[$preferredSlot] = $item;
                continue;
            }

            for ($index = 0; $index < $slotCount; $index++) {
                if ($slots[$index] === null) {
                    $slots[$index] = $item;
                    break;
                }
            }
        }

        return $slots;
    }

    public function getCurrentLevelMapIconAsset(): string
    {
        $level = (int) ($this->gameState['level'] ?? 1);
        $config = LevelConfig::getLevelBySequence($level);

        return LevelConfig::getMapIconAsset($config['mapIcon'] ?? 'hospital');
    }

    public function getLevelLoreEntries(): array
    {
        return (new LoreService())->entriesForLevel((int) ($this->gameState['level'] ?? 1));
    }

    public function getLevelTutorial(): array
    {
        return LevelTutorialConfig::forLevel((int) ($this->gameState['level'] ?? 1));
    }

    private function dispatchTutorialChanged(): void
    {
        $this->dispatch('codesurvivor-tutorial-changed', tutorial: $this->getLevelTutorial());
    }

    /**
     * @return array<string, mixed>
     */
    private function freshAttemptTracker(): array
    {
        return [
            'count' => 0,
            'lastCode' => '',
            'lastFinalPosition' => null,
            'lastDistance' => null,
            'lastErrorType' => null,
            'repeatedSameError' => false,
            'lastProgress' => null,
        ];
    }

    private function finalizeAttempt(GameState $state): void
    {
        if (empty($this->attemptContext)) {
            return;
        }

        $analysis = (new RouteFeedbackService())->analyzeAttempt($state, $this->attemptContext, $this->attemptTracker);

        $this->attemptTracker = array_merge($this->attemptTracker, $analysis['attempt']);
        $this->attemptContext['completed'] = true;
        $this->codeFeedback = $this->appendFeedback($this->codeFeedback, $analysis['feedback'], 'route');
    }

    /**
     * @param array<int, array<string, mixed>> $feedback
     * @param array<string, mixed> $item
     * @return array<int, array<string, mixed>>
     */
    private function appendFeedback(array $feedback, array $item, string $source): array
    {
        $item['source'] = $source;

        return array_slice([...$feedback, $item], -5);
    }

    /**
     * @param array<int, array<string, mixed>> $feedback
     * @param array<string, mixed> $item
     * @return array<int, array<string, mixed>>
     */
    private function prependUniqueFeedback(array $feedback, array $item, string $source): array
    {
        $item['source'] = $source;
        $item['fingerprint'] = $this->feedbackFingerprint($item);

        $filteredFeedback = [];

        foreach ($feedback as $existingItem) {
            $existingFingerprint = $existingItem['fingerprint'] ?? $this->feedbackFingerprint($existingItem);

            if (($existingItem['source'] ?? null) === $source) {
                if ($existingFingerprint === $item['fingerprint']) {
                    return $feedback;
                }

                continue;
            }

            if ($existingFingerprint === $item['fingerprint']) {
                return $feedback;
            }

            $filteredFeedback[] = $existingItem;
        }

        return array_slice([$item, ...$filteredFeedback], 0, 5);
    }

    /**
     * @param array<string, mixed> $item
     */
    private function feedbackFingerprint(array $item): string
    {
        return md5(json_encode([
            'source' => $item['source'] ?? null,
            'type' => $item['type'] ?? null,
            'title' => $item['title'] ?? null,
            'message' => $item['message'] ?? null,
            'suggestion' => $item['suggestion'] ?? null,
            'line' => $item['line'] ?? null,
        ]) ?: '');
    }

    private function hasNewInventoryItem(array $previousInventory, array $currentInventory): bool
    {
        foreach ($currentInventory as $itemId => $item) {
            if (! ($item['collected'] ?? false)) {
                continue;
            }

            if (! isset($previousInventory[$itemId]) || ! ($previousInventory[$itemId]['collected'] ?? false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calcula apenas a visualização do campo de visão; a derrota continua na engine.
     *
     * @return array<string, bool>
     */
    /**
     * Calcula a visualização do campo de visão para renderização.
     */
    public function getVisionCells(): array
    {
        if (($this->gameState['phaseType'] ?? '') !== 'stealth') {
            return [];
        }

        $cells = [];
        $gridSize = $this->gameState['gridSize'] ?? 0;

        foreach ($this->gameState['zombies'] ?? [] as $zombieData) {
            $zombie = Zombie::fromArray($zombieData);

            if ($zombie->visionRange === null || ! $zombie->isAlive()) {
                continue;
            }

            // Otimização: percorre apenas o range do zumbi em vez do mapa inteiro
            $startX = max(0, $zombie->x - $zombie->visionRange);
            $endX   = min($gridSize - 1, $zombie->x + $zombie->visionRange);
            $startY = max(0, $zombie->y - $zombie->visionRange);
            $endY   = min($gridSize - 1, $zombie->y + $zombie->visionRange);

            for ($y = $startY; $y <= $endY; $y++) {
                for ($x = $startX; $x <= $endX; $x++) {
                    if ($zombie->canSeePlayer($x, $y)) {
                        $cells["{$x}:{$y}"] = true;
                    }
                }
            }
        }

        return $cells;
    }

    /**
     * Retorna o asset de cenario.
     */
    public function getSceneryAsset(string $type): string
    {
        return TileVisualConfig::sceneryAssetFor($type, $this->gameState['atmosphere'] ?? 'default');
    }

    /**
     * Retorna a atmosfera atual.
     */
    public function getAtmosphere(): array
    {
        $system = new MovementSystem();
        return $system->getAtmosphereConfig($this->gameState['atmosphere'] ?? 'default');
    }

    /**
     * Retorna a barra de vida do jogador como porcentagem (0-100).
     *
     * @return int
     */
    public function getPlayerHealthPercent(): int
    {
        $player = $this->gameState['player'] ?? [];
        $health    = $player['health']    ?? 0;
        $maxHealth = $player['maxHealth'] ?? 1;

        return (int) round(($health / max(1, $maxHealth)) * 100);
    }

    /**
     * Retorna o asset visual da vida com base no percentual atual.
     */
    public function getPlayerHealthAsset(): string
    {
        $player = $this->gameState['player'] ?? [];
        $health = (int) ($player['health'] ?? 0);
        $maxHealth = max(1, (int) ($player['maxHealth'] ?? 1));

        if ($health >= $maxHealth) {
            return 'hud/health.png';
        }

        if ($health <= max(1, (int) floor($maxHealth / 3))) {
            return 'hud/health_low.png';
        }

        return 'hud/health_medium.png';
    }

    /**
     * Retorna o índice do comando sendo executado no momento (para highlight no editor).
     *
     * @return int Número da linha (1-based), ou 0 se não estiver rodando.
     */
    public function getCurrentCommandLine(): int
    {
        if (! ($this->gameState['isRunning'] ?? false)) {
            return 0;
        }

        return $this->commandQueue[$this->currentStep]['sourceLine'] ?? 0;
    }

    /**
     * Volta para o Mapa de Fases.
     *
     * @return void
     */
    public function backToMap()
    {
        return redirect('/map');
    }

    // =========================================================================
    // RENDER
    // =========================================================================

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.game-board');
    }
}
