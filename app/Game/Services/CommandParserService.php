<?php

namespace App\Game\Services;

use App\Game\Commands\AttackCommand;
use App\Game\Commands\Command;
use App\Game\Commands\MoveCommand;
use App\Game\Commands\WaitCommand;

/**
 * Serviço responsável por analisar o código digitado pelo jogador.
 *
 * Suporta:
 * - Comandos simples: hero.moveRight(), hero.attack()
 * - Parâmetros: hero.moveRight(3)
 * - Loops: repeat(3) { ... } ou for(let i=0; i<3; i++) { ... }
 */
class CommandParserService
{
    private array $errors = [];

    /**
     * Analisa o código e retorna a fila de comandos expandida.
     *
     * @param  string     $code
     * @return Command[]
     */
    public function parse(string $code): array
    {
        $this->errors = [];
        
        // Limpeza básica e normalização
        $code = str_replace(["\r\n", "\r"], "\n", $code);
        $lines = explode("\n", $code);
        
        $result = $this->parseBlock($lines, 0);
        
        // Se houver erros, retorna fila vazia
        if ($this->hasErrors()) {
            return [];
        }

        return $result['commands'];
    }

    /**
     * Analisa um bloco de linhas (suporta recursão para loops).
     *
     * @param string[] $lines
     * @param int      $startIndex
     * @return array{commands: Command[], nextIndex: int}
     */
    private function parseBlock(array $lines, int $startIndex): array
    {
        $commands = [];
        $i = $startIndex;

        while ($i < count($lines)) {
            $line = trim($lines[$i]);
            $lineNumber = $i + 1;

            // Pula vazios e comentários
            if ($line === '' || str_starts_with($line, '//')) {
                $i++;
                continue;
            }

            // Fim do bloco
            if ($line === '}') {
                return ['commands' => $commands, 'nextIndex' => $i + 1];
            }

            // 1. Detectar REPEAT Loop: repeat(N) {
            if (preg_match('/^repeat\s*\(\s*(\d+)\s*\)\s*\{?$/', $line, $matches)) {
                $iterations = (int) $matches[1];
                $blockResult = $this->parseBlock($lines, $i + 1);
                
                for ($j = 0; $j < $iterations; $j++) {
                    $commands = array_merge($commands, $blockResult['commands']);
                }
                
                $i = $blockResult['nextIndex'];
                continue;
            }

            // 2. Detectar FOR Loop: for (let i=0; i<N; i++) {
            if (preg_match('/^for\s*\(.*;\s*i\s*<\s*(\d+)\s*;.*\)\s*\{?$/', $line, $matches)) {
                $iterations = (int) $matches[1];
                $blockResult = $this->parseBlock($lines, $i + 1);
                
                for ($j = 0; $j < $iterations; $j++) {
                    $commands = array_merge($commands, $blockResult['commands']);
                }
                
                $i = $blockResult['nextIndex'];
                continue;
            }

            // 3. Detectar Comandos Individuais
            $command = $this->parseSingleLine($line, $lineNumber);
            if ($command) {
                // Se for um movimento com múltiplos passos, expande em comandos individuais
                if ($command instanceof MoveCommand && $this->getMoveCommandSteps($line) > 1) {
                    $steps = $this->getMoveCommandSteps($line);
                    $direction = $this->getMoveCommandDirection($line);
                    for ($s = 0; $s < $steps; $s++) {
                        $commands[] = new MoveCommand($direction, 1, $lineNumber);
                    }
                } elseif ($command instanceof WaitCommand && $this->getWaitCommandTurns($line) > 1) {
                    $turns = $this->getWaitCommandTurns($line);
                    for ($s = 0; $s < $turns; $s++) {
                        $commands[] = new WaitCommand($lineNumber);
                    }
                } else {
                    $commands[] = $command;
                }
            } else {
                $this->errors[] = ['line' => $lineNumber, 'message' => "Sintaxe inválida na linha {$lineNumber}: \"{$line}\""];
                break; // Para no primeiro erro
            }

            $i++;
        }

        return ['commands' => $commands, 'nextIndex' => $i];
    }

    /**
     * Analisa uma única linha de comando (suporta parâmetros).
     * 
     * Expande movimentos de múltiplos passos em múltiplos comandos individuais
     * para permitir animação suave passo a passo.
     */
    private function parseSingleLine(string $line, int $lineNumber): ?Command
    {
        // hero.moveRight(N)
        if (preg_match('/^hero\.move(Right|Left|Up|Down)\(\s*(\d*)\s*\);?$/i', $line, $matches)) {
            $direction = strtolower($matches[1]);
            $steps = $matches[2] !== '' ? (int)$matches[2] : 1;
            // Retorna apenas um comando com 1 passo; os passos múltiplos serão expandidos em parseBlock
            return new MoveCommand($direction, 1, $lineNumber);
        }

        // hero.attack() ou hero.attack("Nome")
        if (preg_match('/^hero\.attack\(\s*(?:(["\'])([^"\']+)\1)?\s*\);?$/i', $line, $matches)) {
            return new AttackCommand($lineNumber, isset($matches[2]) ? trim($matches[2]) : null);
        }

        if (preg_match('/^hero\.wait\(\s*(\d*)\s*\);?$/i', $line)) {
            return new WaitCommand($lineNumber);
        }

        return null;
    }

    public function getErrors(): array { return $this->errors; }
    public function hasErrors(): bool { return ! empty($this->errors); }
    public function getFirstError(): string { return $this->errors[0]['message'] ?? ''; }

    /**
     * Extrai o número de passos de uma linha de movimento.
     */
    private function getMoveCommandSteps(string $line): int
    {
        if (preg_match('/^hero\.move(Right|Left|Up|Down)\(\s*(\d*)\s*\);?$/i', $line, $matches)) {
            return $matches[2] !== '' ? (int)$matches[2] : 1;
        }
        return 1;
    }

    /**
     * Extrai a direção de uma linha de movimento.
     */
    private function getMoveCommandDirection(string $line): string
    {
        if (preg_match('/^hero\.move(Right|Left|Up|Down)\(\s*(\d*)\s*\);?$/i', $line, $matches)) {
            return strtolower($matches[1]);
        }
        return 'down';
    }

    private function getWaitCommandTurns(string $line): int
    {
        if (preg_match('/^hero\.wait\(\s*(\d*)\s*\);?$/i', $line, $matches)) {
            return $matches[1] !== '' ? max(1, (int) $matches[1]) : 1;
        }

        return 1;
    }

    public function serializeCommands(array $commands): array
    {
        return array_map(fn(Command $cmd) => $cmd->toArray(), $commands);
    }

    public function deserializeCommands(array $data): array
    {
        $commands = [];
        foreach ($data as $item) {
            $command = match ($item['type'] ?? '') {
                'move'   => MoveCommand::fromArray($item),
                'attack' => AttackCommand::fromArray($item),
                'wait'   => WaitCommand::fromArray($item),
                default  => null,
            };
            if ($command) $commands[] = $command;
        }
        return $commands;
    }
}
