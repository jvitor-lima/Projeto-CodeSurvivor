<?php

namespace App\Game\Config;

class LevelTutorialConfig
{
    /**
     * @return array<string, mixed>
     */
    public static function forLevel(int $level): array
    {
        $tutorials = self::tutorials();

        return ['level' => $level] + ($tutorials[$level] ?? self::defaultTutorial($level));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function tutorials(): array
    {
        return [
            1 => [
                'title' => 'Primeiros comandos',
                'summary' => 'Escreva comandos simples para guiar Leon ate a saida.',
                'briefingTitle' => 'Fase 1 - Primeira rota',
                'briefingIcon' => 'target',
                'mechanic' => 'Movimento basico',
                'danger' => 'Sem inimigos',
                'objective' => 'Leve Leon ate o ponto de saida usando comandos de movimento.',
                'commands' => [
                    ['code' => 'hero.moveDown()', 'description' => 'Baixo', 'icon' => 'arrow-d'],
                    ['code' => 'hero.moveRight()', 'description' => 'Direita', 'icon' => 'arrow-r'],
                    ['code' => 'hero.moveLeft()', 'description' => 'Esquerda', 'icon' => 'arrow-l'],
                    ['code' => 'hero.moveUp()', 'description' => 'Cima', 'icon' => 'arrow-u'],
                    ['code' => 'hero.moveDown(2)', 'description' => 'Dois passos', 'icon' => 'loop'],
                ],
                'quickTips' => [
                    'Ande pelas casas livres.',
                    'O marcador verde indica a saida.',
                    'Uma linha de codigo executa uma acao.',
                ],
                'steps' => [
                    [
                        'title' => 'Controle Leon com codigo',
                        'body' => 'Leon so se move quando voce escreve comandos no editor.',
                        'focus' => 'board',
                        'icon' => 'hero',
                        'action' => 'Observe o ponto inicial.',
                    ],
                    [
                        'title' => 'Mire na saida',
                        'body' => 'Siga o caminho ate o marcador verde da fase.',
                        'focus' => 'objective',
                        'icon' => 'target',
                        'action' => 'Chegue ao objetivo.',
                    ],
                    [
                        'title' => 'Escreva comandos',
                        'body' => 'Use o editor. Cada linha vira um movimento no tabuleiro.',
                        'focus' => 'editor',
                        'icon' => 'terminal',
                        'action' => 'Exemplo: hero.moveDown()',
                    ],
                    [
                        'title' => 'Use atalhos',
                        'body' => 'Clique nos comandos prontos para montar sua primeira rota.',
                        'focus' => 'commands',
                        'icon' => 'arrows',
                        'action' => 'Toque em um comando.',
                    ],
                    [
                        'title' => 'Execute o codigo',
                        'body' => 'Aperte executar e veja Leon seguir sua sequencia.',
                        'focus' => 'run',
                        'icon' => 'play',
                        'action' => 'Clique em Executar.',
                    ],
                    [
                        'title' => 'Melhore a solucao',
                        'body' => 'Movimentos repetidos podem virar um comando mais curto.',
                        'focus' => 'feedback',
                        'icon' => 'light',
                        'action' => 'Use hero.moveDown(3).',
                    ],
                ],
            ],
            2 => [
                'title' => 'Itens na rota',
                'summary' => 'Pegue o item, volte para a rota e chegue a saida.',
                'briefingTitle' => 'Fase 2 - Coleta de suprimentos',
                'briefingIcon' => 'bag',
                'mechanic' => 'Item obrigatorio',
                'danger' => 'Rota lateral',
                'objective' => 'Colete o bastao antes de sair da fase.',
                'commands' => [
                    ['code' => 'hero.moveUp(2)', 'description' => 'Avancar', 'icon' => 'arrow-u'],
                    ['code' => 'hero.moveLeft(2)', 'description' => 'Coletar', 'icon' => 'arrow-l'],
                    ['code' => 'hero.moveRight(2)', 'description' => 'Voltar', 'icon' => 'arrow-r'],
                ],
                'quickTips' => [
                    'Algumas saidas exigem itens.',
                    'Use numeros para mover varias casas.',
                ],
                'steps' => [
                    [
                        'title' => 'Pegue o item',
                        'body' => 'A saida so conta depois que Leon passa pelo item.',
                        'focus' => 'board',
                        'icon' => 'bag',
                        'action' => 'Passe pelo item.',
                    ],
                    [
                        'title' => 'Use parametros',
                        'body' => 'Ande varias casas com um numero.',
                        'focus' => 'editor',
                        'icon' => 'loop',
                        'action' => 'Teste hero.moveUp(2).',
                    ],
                ],
            ],
            3 => [
                'title' => 'Primeiro combate',
                'summary' => 'Pare. Ataque. Depois avance.',
                'briefingTitle' => 'Fase 3 - Primeiro infectado',
                'briefingIcon' => 'attack',
                'mechanic' => 'Ataque frontal',
                'danger' => 'Inimigo na rota',
                'objective' => 'Elimine o infectado antes de atravessar o bloqueio.',
                'commands' => [
                    ['code' => 'hero.attack()', 'description' => 'Atacar', 'icon' => 'attack'],
                    ['code' => 'hero.moveRight()', 'description' => 'Reposicionar', 'icon' => 'arrow-r'],
                ],
                'quickTips' => [
                    'Ataque antes de tentar passar.',
                    'A posicao de Leon importa.',
                ],
                'steps' => [
                    [
                        'title' => 'Neutralize primeiro',
                        'body' => 'Um inimigo vivo bloqueia a rota de Leon.',
                        'focus' => 'board',
                        'icon' => 'attack',
                        'action' => 'Use hero.attack().',
                    ],
                    [
                        'title' => 'Leia o retorno',
                        'body' => 'O registro mostra o que aconteceu.',
                        'focus' => 'feedback',
                        'icon' => 'info',
                        'action' => 'Ajuste e tente de novo.',
                    ],
                ],
            ],
            4 => [
                'title' => 'Campo de visao',
                'summary' => 'Evite o Garrador. Planeje antes de executar.',
                'briefingTitle' => 'Fase 4 - Fuga do Garrador',
                'briefingIcon' => 'target',
                'mechanic' => 'Stealth',
                'danger' => 'Campo de visao',
                'objective' => 'Atravesse o laboratorio sem entrar no campo de visao.',
                'commands' => [
                    ['code' => 'hero.moveDown()', 'description' => 'Avancar', 'icon' => 'arrow-d'],
                    ['code' => 'hero.moveLeft()', 'description' => 'Desviar', 'icon' => 'arrow-l'],
                    ['code' => 'hero.moveRight()', 'description' => 'Retomar', 'icon' => 'arrow-r'],
                ],
                'quickTips' => [
                    'Nao entre na area marcada.',
                    'Planeje antes de executar.',
                    'Nem todo inimigo deve ser enfrentado.',
                ],
                'steps' => [
                    [
                        'title' => 'Evite a visao',
                        'body' => 'Casas marcadas significam perigo imediato.',
                        'focus' => 'board',
                        'icon' => 'warning',
                        'action' => 'Fique fora da zona.',
                    ],
                    [
                        'title' => 'Planeje a rota',
                        'body' => 'Execute, observe e ajuste.',
                        'focus' => 'editor',
                        'icon' => 'terminal',
                        'action' => 'Teste com calma.',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function defaultTutorial(int $level): array
    {
        return [
            'title' => "Ajuda da fase {$level}",
            'summary' => 'Leia o objetivo, escreva comandos e execute a rota.',
            'briefingTitle' => "Fase {$level} - Rota ativa",
            'briefingIcon' => 'target',
            'mechanic' => 'Sequencia de comandos',
            'danger' => 'Analise a rota',
            'objective' => 'Monte uma sequencia de comandos para concluir a fase.',
            'commands' => [
                ['code' => 'hero.moveRight()', 'description' => 'Direita', 'icon' => 'arrow-r'],
                ['code' => 'hero.moveLeft()', 'description' => 'Esquerda', 'icon' => 'arrow-l'],
                ['code' => 'hero.moveUp()', 'description' => 'Cima', 'icon' => 'arrow-u'],
                ['code' => 'hero.moveDown()', 'description' => 'Baixo', 'icon' => 'arrow-d'],
                ['code' => 'hero.attack()', 'description' => 'Ataque', 'icon' => 'attack'],
            ],
            'quickTips' => [
                'Uma linha de codigo executa uma acao.',
                'Execute para testar sua rota.',
                'Use o feedback para ajustar.',
            ],
            'steps' => [
                [
                    'title' => 'Leia o objetivo',
                    'body' => 'A meta guia sua rota.',
                    'focus' => 'objective',
                    'icon' => 'target',
                    'action' => 'Ache a saida.',
                ],
                [
                    'title' => 'Monte sua sequencia',
                    'body' => 'O jogo executa seus comandos de cima para baixo.',
                    'focus' => 'editor',
                    'icon' => 'terminal',
                    'action' => 'Escreva e execute.',
                ],
            ],
        ];
    }
}
