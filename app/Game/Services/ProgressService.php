<?php

namespace App\Game\Services;

use App\Game\Config\LevelConfig;

/**
 * Serviço responsável por gerenciar o progresso do jogador nas fases.
 *
 * Responsabilidades:
 * - Carregar progresso da sessão/cache.
 * - Salvar progresso após conclusão de uma fase.
 * - Verificar se uma fase está desbloqueada.
 * - Desbloquear a próxima fase após conclusão.
 */
class ProgressService
{
    /**
     * Chave da sessão onde o progresso é armazenado.
     */
    private const PROGRESS_SESSION_KEY = 'level_progress';

    /**
     * Carrega o progresso do jogador da sessão.
     * Se não existir, inicializa com a Fase 1 disponível.
     *
     * @return array<string, string> Progresso: ['1' => 'available', '2' => 'locked', ...]
     */
    public function loadProgress(): array
    {
        $progress = session(self::PROGRESS_SESSION_KEY);

        if ($progress === null) {
            $progress = $this->initializeProgress();
            session([self::PROGRESS_SESSION_KEY => $progress]);
        }

        $progress = $this->normalizeProgress($progress);
        session([self::PROGRESS_SESSION_KEY => $progress]);

        return $progress;
    }

    /**
     * Inicializa o progresso padrão (Fase 1 disponível, resto bloqueado).
     *
     * @return array<string, string>
     */
    private function initializeProgress(): array
    {
        $progress = [];

        foreach (array_keys(LevelConfig::getAllLevels()) as $level) {
            $progress[(string) $level] = $level === 1 ? 'available' : 'locked';
        }

        return $progress;
    }

    private function normalizeProgress(array $progress): array
    {
        $levels = array_keys(LevelConfig::getAllLevels());

        foreach ($levels as $level) {
            $key = (string) $level;

            if (isset($progress[$key])) {
                continue;
            }

            $previousKey = (string) ($level - 1);
            $progress[$key] = ($level === 1 || ($progress[$previousKey] ?? null) === 'completed')
                ? 'available'
                : 'locked';
        }

        return array_intersect_key($progress, array_flip(array_map('strval', $levels)));
    }

    /**
     * Marca uma fase como concluída e desbloqueia a próxima.
     *
     * @param  int $level Número da fase concluída.
     * @return void
     */
    public function completeLevel(int $level): void
    {
        $progress = $this->loadProgress();

        // Marca a fase como concluída
        $progress[$level] = 'completed';

        // Desbloqueia a próxima fase se existir
        if (isset($progress[$level + 1])) {
            $progress[$level + 1] = 'available';
        }

        // Salva o progresso atualizado
        session([self::PROGRESS_SESSION_KEY => $progress]);
    }

    /**
     * Retorna o status de uma fase específica.
     *
     * @param  int $level Número da fase.
     * @return string Status: 'completed' | 'available' | 'locked'
     */
    public function getLevelStatus(int $level): string
    {
        $progress = $this->loadProgress();
        return $progress[$level] ?? 'locked';
    }

    /**
     * Verifica se uma fase está desbloqueada (disponível ou concluída).
     *
     * @param  int $level Número da fase.
     * @return bool
     */
    public function isLevelUnlocked(int $level): bool
    {
        $status = $this->getLevelStatus($level);
        return $status === 'available' || $status === 'completed';
    }

    /**
     * Retorna o progresso completo do jogador.
     *
     * @return array<string, string>
     */
    public function getProgress(): array
    {
        return $this->loadProgress();
    }

    /**
     * Reseta o progresso do jogador (útil para testes ou reinicialização).
     *
     * @return void
     */
    public function resetProgress(): void
    {
        session([self::PROGRESS_SESSION_KEY => $this->initializeProgress()]);
    }
}
