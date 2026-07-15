<?php

namespace App\Game\Services;

class CodeFeedbackService
{
    private const MOVE_PATTERN = '/^hero\.move(Right|Left|Up|Down)\(\s*(\d*)\s*\);?$/i';
    private const ATTACK_PATTERN = '/^hero\.attack\(\s*(?:(["\'])([^"\']+)\1)?\s*\);?$/i';
    private const WAIT_PATTERN = '/^hero\.wait\(\s*(\d*)\s*\);?$/i';
    private const REPEAT_PATTERN = '/^repeat\s*\(\s*\d+\s*\)\s*\{?$/i';
    private const FOR_PATTERN = '/^for\s*\(.*;\s*i\s*<\s*\d+\s*;.*\)\s*\{?$/i';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function analyze(string $code): array
    {
        $code = str_replace(["\r\n", "\r"], "\n", $code);

        if (trim($code) === '') {
            return [[
                'type' => 'info',
                'category' => 'Dica',
                'title' => 'Comece com um comando',
                'message' => 'Digite pelo menos um comando. Leon precisa de uma acao para sair do lugar.',
                'suggestion' => [
                    'label' => 'Experimente',
                    'to' => 'hero.moveDown()',
                ],
                'line' => null,
            ]];
        }

        $feedback = [];
        $lines = explode("\n", $code);
        $commandLines = $this->commandLines($lines);

        foreach ($commandLines as $lineData) {
            $syntaxFeedback = $this->syntaxFeedback($lineData['code'], $lineData['line']);

            if ($syntaxFeedback !== null) {
                $feedback[] = $syntaxFeedback;
                break;
            }
        }

        foreach ($this->repeatedMovementFeedback($commandLines) as $tip) {
            $feedback[] = $tip;
        }

        if (count($commandLines) >= 8 && ! $this->usesMovementParameters($commandLines)) {
            $feedback[] = [
                'type' => 'optimization',
                'category' => 'Otimizacao',
                'title' => 'Solucao longa',
                'message' => 'Voce pode agrupar movimentos repetidos e deixar a rota mais limpa.',
                'suggestion' => [
                    'label' => 'Exemplo',
                    'to' => 'hero.moveRight(3)',
                ],
                'line' => null,
            ];
        }

        return array_slice($feedback, 0, 4);
    }

    /**
     * @param string[] $lines
     * @return array<int, array{line: int, code: string}>
     */
    private function commandLines(array $lines): array
    {
        $commandLines = [];

        foreach ($lines as $index => $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '//')) {
                continue;
            }

            $commandLines[] = [
                'line' => $index + 1,
                'code' => $trimmed,
            ];
        }

        return $commandLines;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function syntaxFeedback(string $line, int $lineNumber): ?array
    {
        if ($line === '}' || preg_match(self::MOVE_PATTERN, $line) || preg_match(self::ATTACK_PATTERN, $line) || preg_match(self::WAIT_PATTERN, $line)) {
            return null;
        }

        if (preg_match(self::REPEAT_PATTERN, $line) || preg_match(self::FOR_PATTERN, $line)) {
            return null;
        }

        if (preg_match('/^hero\.move(Right|Left|Up|Down)\s*;?$/i', $line, $matches)) {
            return [
                'type' => 'warning',
                'category' => 'Erro de comando',
                'title' => 'Faltam parenteses',
                'message' => "Linha {$lineNumber}: o jogo precisa dos parenteses para executar.",
                'suggestion' => [
                    'label' => 'Use',
                    'to' => "hero.move{$matches[1]}()",
                ],
                'line' => $lineNumber,
            ];
        }

        if (preg_match('/^hero\.attack\s*;?$/i', $line)) {
            return [
                'type' => 'warning',
                'category' => 'Erro de comando',
                'title' => 'Ataque incompleto',
                'message' => "Linha {$lineNumber}: faltam os parenteses da acao.",
                'suggestion' => [
                    'label' => 'Use',
                    'to' => 'hero.attack()',
                ],
                'line' => $lineNumber,
            ];
        }

        if (preg_match('/^move\.hero(Right|Left|Up|Down)\(\s*(\d*)\s*\);?$/i', $line, $matches)) {
            $steps = $matches[2] !== '' ? $matches[2] : '';

            return [
                'type' => 'warning',
                'category' => 'Erro de comando',
                'title' => 'Ordem invertida',
                'message' => "Na linha {$lineNumber}, a ordem do comando esta invertida. Tente hero.move{$matches[1]}({$steps}).",
                'suggestion' => [
                    'label' => 'Troque por',
                    'from' => $line,
                    'to' => "hero.move{$matches[1]}({$steps})",
                ],
                'line' => $lineNumber,
            ];
        }

        if (preg_match('/^attack\(\s*\)\s*;?$/i', $line)) {
            return [
                'type' => 'warning',
                'category' => 'Erro de comando',
                'title' => 'Quem ataca?',
                'message' => "Linha {$lineNumber}: indique que Leon executa a acao.",
                'suggestion' => [
                    'label' => 'Use',
                    'from' => $line,
                    'to' => 'hero.attack()',
                ],
                'line' => $lineNumber,
            ];
        }

        if (preg_match('/^wait\(\s*(\d*)\s*\)\s*;?$/i', $line, $matches)) {
            $turns = $matches[1] ?? '';

            return [
                'type' => 'warning',
                'category' => 'Erro de comando',
                'title' => 'Quem espera?',
                'message' => "Linha {$lineNumber}: indique que Leon executa a espera.",
                'suggestion' => [
                    'label' => 'Use',
                    'from' => $line,
                    'to' => "hero.wait({$turns})",
                ],
                'line' => $lineNumber,
            ];
        }

        if (str_contains($line, 'hero.move') || str_contains($line, 'hero.attack') || str_contains($line, 'hero.wait')) {
            return [
                'type' => 'warning',
                'category' => 'Atencao',
                'title' => 'Revise a sintaxe',
                'message' => "Linha {$lineNumber}: confira ponto, nome e parenteses.",
                'suggestion' => [
                    'label' => 'Exemplo valido',
                    'to' => 'hero.moveDown()',
                ],
                'line' => $lineNumber,
            ];
        }

        return [
            'type' => 'warning',
            'category' => 'Erro de comando',
            'title' => 'Comando desconhecido',
            'message' => "Linha {$lineNumber}: esse comando nao existe no jogo.",
            'suggestion' => [
                'label' => 'Comandos validos',
                'to' => 'hero.moveDown(), hero.attack(), hero.wait()',
            ],
            'line' => $lineNumber,
        ];
    }

    /**
     * @param array<int, array{line: int, code: string}> $commandLines
     * @return array<int, array<string, mixed>>
     */
    private function repeatedMovementFeedback(array $commandLines): array
    {
        $feedback = [];
        $previousDirection = null;
        $startLine = null;
        $count = 0;

        foreach ($commandLines as $lineData) {
            $direction = $this->singleStepMoveDirection($lineData['code']);

            if ($direction !== null && $direction === $previousDirection) {
                $count++;
                continue;
            }

            $this->pushRepeatedMovementFeedback($feedback, $previousDirection, $count, $startLine);

            $previousDirection = $direction;
            $startLine = $direction === null ? null : $lineData['line'];
            $count = $direction === null ? 0 : 1;
        }

        $this->pushRepeatedMovementFeedback($feedback, $previousDirection, $count, $startLine);

        return $feedback;
    }

    /**
     * @param array<int, array<string, mixed>> $feedback
     */
    private function pushRepeatedMovementFeedback(array &$feedback, ?string $direction, int $count, ?int $startLine): void
    {
        if ($direction === null || $count < 2 || $startLine === null) {
            return;
        }

        $method = 'hero.move' . ucfirst(strtolower($direction));
        $timesText = $count === 2 ? 'duas vezes' : "{$count} vezes";

        $feedback[] = [
            'type' => 'optimization',
            'category' => 'Otimizacao',
            'title' => 'Melhore a rota',
            'message' => "Voce repetiu {$method}() {$timesText}. Use {$method}({$count}).",
            'suggestion' => [
                'label' => 'Troque por',
                'from' => implode("\n", array_fill(0, $count, "{$method}()")),
                'to' => "{$method}({$count})",
            ],
            'line' => $startLine,
        ];
    }

    private function singleStepMoveDirection(string $line): ?string
    {
        if (! preg_match(self::MOVE_PATTERN, $line, $matches)) {
            return null;
        }

        $steps = $matches[2] !== '' ? (int) $matches[2] : 1;

        return $steps === 1 ? $matches[1] : null;
    }

    /**
     * @param array<int, array{line: int, code: string}> $commandLines
     */
    private function usesMovementParameters(array $commandLines): bool
    {
        foreach ($commandLines as $lineData) {
            if (preg_match(self::MOVE_PATTERN, $lineData['code'], $matches) && ($matches[2] ?? '') !== '') {
                return true;
            }
        }

        return false;
    }
}
