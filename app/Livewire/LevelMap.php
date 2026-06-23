<?php

namespace App\Livewire;

use App\Game\Config\LevelConfig;
use App\Game\Services\ProgressService;
use Livewire\Component;

/**
 * Componente Livewire para o Mapa de Fases.
 *
 * Exibe todas as fases disponíveis com seus status (bloqueada, disponível, concluída)
 * e permite a navegação entre elas, seguindo uma progressão linear.
 *
 * Regras de desbloqueio:
 * - Fase 1 sempre está disponível.
 * - Fases subsequentes desbloqueiam apenas após a conclusão da fase anterior.
 * - Fases concluídas podem ser revisitadas.
 */
class LevelMap extends Component
{
    /**
     * Progresso do jogador: array com o status de cada fase.
     * Formato: ['1' => 'completed', '2' => 'available', '3' => 'locked']
     *
     * @var array<string, string>
     */
    public array $levelProgress = [];

    /**
     * Fase atualmente selecionada (para destaque visual).
     *
     * @var int|null
     */
    public ?int $selectedLevel = null;

    /**
     * Mapa/campanha selecionado na tela de fases.
     *
     * @var int
     */
    public int $selectedMap = 1;

    /**
     * Inicializa o componente carregando o progresso da sessão/cache.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->loadProgress();

        $requestedMap = (int) request()->query('map', 0);
        $this->selectedMap = $requestedMap > 0 && $this->isMapUnlocked($requestedMap)
            ? $requestedMap
            : LevelConfig::getFirstIncompleteMap($this->levelProgress);
    }

    /**
     * Carrega o progresso do jogador da sessão.
     * Se não existir, inicializa com a Fase 1 disponível.
     *
     * @return void
     */
    private function loadProgress(): void
    {
        $this->levelProgress = (new ProgressService())->loadProgress();
    }

    /**
     * Marca uma fase como concluída e desbloqueia a próxima.
     *
     * Chamado quando o jogador vence uma fase no GameBoard.
     *
     * @param  int $level Número da fase concluída.
     * @return void
     */
    public function completeLevel(int $level): void
    {
        $this->levelProgress[$level] = 'completed';

        // Desbloqueia a próxima fase se existir
        if (isset($this->levelProgress[$level + 1])) {
            $this->levelProgress[$level + 1] = 'available';
        }

        session(['level_progress' => $this->levelProgress]);
    }

    /**
     * Seleciona uma fase e redireciona para o GameBoard.
     *
     * Apenas fases disponíveis ou concluídas podem ser selecionadas.
     *
     * @param  int $level Número da fase.
     * @return void
     */
    public function selectLevel(int $level)
    {
        $status = $this->levelProgress[$level] ?? 'locked';

        if ($status === 'locked') {
            $this->dispatch('notify', message: 'Esta fase ainda esta bloqueada. Conclua a fase anterior primeiro.', type: 'error');
            return;
        }

        $this->selectedLevel = $level;
        // Redireciona para o GameBoard com a fase selecionada
        return redirect('/game?level=' . $level);
    }

    public function selectMap(int $mapId): void
    {
        if (! $this->isMapUnlocked($mapId)) {
            $this->dispatch('notify', message: 'Este mapa ainda esta bloqueado. Avance na campanha para liberar.', type: 'error');
            return;
        }

        $this->selectedMap = $mapId;
        $this->selectedLevel = null;
    }

    /**
     * Retorna a lista de todas as fases com seus metadados.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLevels(): array
    {
        $levels = LevelConfig::getLevelsForMap($this->selectedMap);
        
        // Enriquece com dados detalhados da config real (icones, localizacao)
        foreach ($levels as $id => &$level) {
            $config = LevelConfig::getLevelBySequence($id);
            $level['mapIcon'] = $config['mapIcon'] ?? 'hospital';
            $level['mapLocation'] = $config['mapLocation'] ?? ['x' => 0, 'y' => 0];
            $level['difficulty'] = $level['difficulty'] ?? ($config['difficulty'] ?? 'Normal');
            $level['mapLevel'] = LevelConfig::getMapLevelNumber($id);
        }

        return $levels;
    }

    public function getCampaignMaps(): array
    {
        $maps = LevelConfig::getCampaignMaps();

        foreach ($maps as $id => &$map) {
            $map['unlocked'] = $this->isMapUnlocked((int) $id);
            $map['completion'] = LevelConfig::mapCompletion((int) $id, $this->levelProgress);
        }

        return $maps;
    }

    public function getSelectedMap(): array
    {
        return LevelConfig::getMapById($this->selectedMap);
    }

    public function getSelectedMapBackgroundAsset(): string
    {
        $background = LevelConfig::getMapBackground($this->selectedMap);

        return file_exists(public_path($background))
            ? $background
            : LevelConfig::getCampaignMaps()[1]['background'];
    }

    public function getSelectedMapCompletion(): array
    {
        return LevelConfig::mapCompletion($this->selectedMap, $this->levelProgress);
    }

    /**
     * Retorna o asset do icone do mapa.
     */
    public function getMapIconAsset(string $icon): string
    {
        return LevelConfig::getMapIconAsset($icon);
    }

    public function isMapUnlocked(int $mapId): bool
    {
        return LevelConfig::isMapUnlocked($mapId, $this->levelProgress);
    }

    /**
     * Retorna o status de uma fase específica.
     *
     * @param  int $level Número da fase.
     * @return string Status: 'completed' | 'available' | 'locked'
     */
    public function getLevelStatus(int $level): string
    {
        return $this->levelProgress[$level] ?? 'locked';
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.level-map');
    }
}
