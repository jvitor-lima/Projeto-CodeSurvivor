<?php

namespace App\Game\Services;

use App\Game\Engine\GameState;

class RouteFeedbackService
{
    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $tracker
     * @return array{feedback: array<string, mixed>, attempt: array<string, mixed>}
     */
    public function analyzeAttempt(GameState $state, array $context, array $tracker = []): array
    {
        $finalPosition = ['x' => $state->player->x, 'y' => $state->player->y];
        $finalDistance = $this->distance($finalPosition, ['x' => $state->goal->x, 'y' => $state->goal->y]);
        $startDistance = (int) ($context['startDistance'] ?? $finalDistance);
        $previousDistance = $context['previousDistance'] ?? null;
        $attemptNumber = (int) ($context['attemptNumber'] ?? 1);
        $blocked = $this->hasBlockedMovement($state->log);

        $errorType = $this->errorType($state, $finalDistance, $startDistance, $blocked);
        $repeatedSameError = ! empty($tracker['lastErrorType'])
            && $tracker['lastErrorType'] === $errorType
            && $errorType !== 'success';

        $attempt = [
            'count' => $attemptNumber,
            'lastCode' => $context['code'] ?? '',
            'lastFinalPosition' => $finalPosition,
            'lastDistance' => $finalDistance,
            'lastErrorType' => $errorType,
            'repeatedSameError' => $repeatedSameError,
            'lastProgress' => $this->progressLabel($finalDistance, $startDistance, $previousDistance),
        ];

        return [
            'feedback' => $this->feedbackCard($state, $attempt, $startDistance, $previousDistance, $blocked),
            'attempt' => $attempt,
        ];
    }

    /**
     * @param array<string, mixed> $tracker
     * @param array<string, mixed> $gameState
     * @return array<string, mixed>
     */
    public function stuckHint(int $level, array $tracker, array $gameState): array
    {
        $attempts = (int) ($tracker['count'] ?? 0);
        $lastError = $tracker['lastErrorType'] ?? 'unknown';
        $hint = $this->progressiveHint($level, max(4, $attempts + 1), $lastError, (bool) ($tracker['repeatedSameError'] ?? false));

        return [
            'type' => 'hint',
            'category' => 'Pista',
            'title' => 'Preciso de uma pista',
            'message' => $hint,
            'suggestion' => [
                'label' => 'Proximo passo',
                'to' => $this->stuckSuggestion($level, $attempts, $gameState),
            ],
            'line' => null,
        ];
    }

    /**
     * @param array{x:int, y:int} $from
     * @param array{x:int, y:int} $to
     */
    public function distance(array $from, array $to): int
    {
        return abs((int) $from['x'] - (int) $to['x']) + abs((int) $from['y'] - (int) $to['y']);
    }

    /**
     * @param string[] $log
     */
    private function hasBlockedMovement(array $log): bool
    {
        foreach ($log as $entry) {
            if (str_contains($entry, 'Movimento bloqueado')) {
                return true;
            }
        }

        return false;
    }

    private function errorType(GameState $state, int $finalDistance, int $startDistance, bool $blocked): string
    {
        if ($state->win || $finalDistance === 0) {
            return 'success';
        }

        if ($blocked) {
            return 'blocked';
        }

        if ($finalDistance > $startDistance) {
            return 'farther';
        }

        if ($finalDistance === $startDistance) {
            return 'same_distance';
        }

        return 'incomplete';
    }

    private function progressLabel(int $finalDistance, int $startDistance, ?int $previousDistance): string
    {
        if ($finalDistance === 0) {
            return 'reached';
        }

        if ($previousDistance !== null) {
            if ($finalDistance < $previousDistance) {
                return 'closer_than_last';
            }

            if ($finalDistance > $previousDistance) {
                return 'farther_than_last';
            }
        }

        if ($finalDistance < $startDistance) {
            return 'closer';
        }

        if ($finalDistance > $startDistance) {
            return 'farther';
        }

        return 'same';
    }

    /**
     * @param array<string, mixed> $attempt
     * @return array<string, mixed>
     */
    private function feedbackCard(GameState $state, array $attempt, int $startDistance, ?int $previousDistance, bool $blocked): array
    {
        $finalDistance = (int) $attempt['lastDistance'];
        $level = (int) $state->level;
        $attemptNumber = (int) $attempt['count'];
        $errorType = (string) $attempt['lastErrorType'];

        if ($state->win || $finalDistance === 0) {
            return [
                'type' => 'success',
                'category' => 'Progresso',
                'title' => 'Objetivo alcancado',
                'message' => 'Boa. Sua sequencia levou Leon ate a saida.',
                'suggestion' => ['label' => 'Resultado', 'to' => 'Fase concluida'],
                'line' => null,
            ];
        }

        $message = match (true) {
            $blocked => 'Esse caminho esta bloqueado. Procure uma casa livre para contornar.',
            $previousDistance !== null && $finalDistance < $previousDistance => 'Boa, voce se aproximou do objetivo.',
            $previousDistance !== null && $finalDistance > $previousDistance => 'Voce se afastou do objetivo. Reavalie a direcao.',
            $finalDistance <= 2 => 'Quase la. Ajuste os ultimos passos da rota.',
            $finalDistance < $startDistance => 'Boa, Leon chegou mais perto da saida.',
            $finalDistance > $startDistance => 'Leon foi para longe do objetivo. Olhe a posicao da saida.',
            default => 'Sua tentativa terminou na mesma distancia. Mude a rota.',
        };

        return [
            'type' => $blocked ? 'warning' : 'hint',
            'category' => $blocked ? 'Rota bloqueada' : 'Dica de rota',
            'title' => $this->titleFor($errorType),
            'message' => $message,
            'suggestion' => [
            'label' => 'Pista',
                'to' => $this->progressiveHint($level, $attemptNumber, $errorType, (bool) $attempt['repeatedSameError']),
            ],
            'line' => null,
        ];
    }

    private function titleFor(string $errorType): string
    {
        return match ($errorType) {
            'blocked' => 'Caminho bloqueado',
            'farther' => 'Direcao errada',
            'same_distance' => 'Rota sem ganho',
            default => 'A rota ainda nao fechou',
        };
    }

    private function progressiveHint(int $level, int $attemptNumber, string $errorType, bool $repeatedSameError): string
    {
        if ($level === 1) {
            if ($attemptNumber <= 1) {
                return 'Observe onde esta a saida antes de escrever.';
            }

            if ($attemptNumber === 2 || $errorType === 'blocked') {
                return 'Voce precisa descer antes de seguir para a direita.';
            }

            if ($attemptNumber === 3 || $repeatedSameError) {
                return 'Combine movimentos verticais e horizontais.';
            }

            return 'Pista forte: primeiro desca pela trilha, depois vire para a direita.';
        }

        if ($attemptNumber <= 1) {
            return 'Compare a posicao final com o objetivo e ajuste uma parte.';
        }

        if ($attemptNumber <= 3) {
            return 'Tente mudar apenas o trecho onde a rota parou de ajudar.';
        }

        return 'Use o registro da missao para encontrar o ultimo movimento util.';
    }

    /**
     * @param array<string, mixed> $gameState
     */
    private function stuckSuggestion(int $level, int $attempts, array $gameState): string
    {
        if ($level === 1 && $attempts >= 3) {
            return 'Comece descendo pela trilha principal. Depois avance para a direita.';
        }

        if ($level === 1) {
            return 'Procure uma rota que desce primeiro.';
        }

        return ($gameState['objective'] ?? 'Releia o objetivo e ajuste a rota.');
    }
}
