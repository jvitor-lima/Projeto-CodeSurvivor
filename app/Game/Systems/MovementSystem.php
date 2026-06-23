<?php

namespace App\Game\Systems;

use App\Game\Engine\GameState;
use App\Game\Entities\Player;

/**
 * Sistema responsável por toda lógica de movimentação do jogo.
 *
 * O MovementSystem valida se um movimento é possível (limites do mapa, tipo de tile)
 * e aplica a mudança de posição e direção ao Player (ou qualquer entidade móvel no futuro).
 *
 * Regras de movimento:
 * - O personagem só pode se mover para tiles do tipo 'caminho'.
 * - Não pode ultrapassar os limites do grid.
 */
class MovementSystem
{
    /**
     * Tenta mover o jogador na direção especificada.
     *
     * Atualiza a direção visual do jogador independentemente de o movimento ser válido.
     * Só atualiza a posição se o destino for um tile de 'caminho' dentro dos limites.
     *
     * @param GameState $state     Estado atual do jogo.
     * @param string    $direction Direção do movimento: 'up' | 'down' | 'left' | 'right'
     * @return bool                Retorna true se o movimento foi executado com sucesso.
     */
    public function movePlayer(GameState $state, string $direction): bool
    {
        $player = $state->player;

        [$newX, $newY, $facingDirection] = $this->calculateTarget($player->x, $player->y, $direction);

        // Atualiza a direção visual mesmo que o movimento seja bloqueado.
        $player->direction = $facingDirection;

        if (! $this->isValidPosition($state, $newX, $newY)) {
            $state->addLog("Movimento bloqueado: ({$newX}, {$newY}) não é acessível.");
            return false;
        }

        $player->x = $newX;
        $player->y = $newY;

        $state->addLog("Herói moveu para {$direction}: ({$newX}, {$newY}).");

        (new CollectibleSystem())->collectAtPlayer($state);

        return true;
    }

    /**
     * Calcula as coordenadas de destino e a direção visual para um movimento.
     *
     * @param  int    $x         Posição X atual.
     * @param  int    $y         Posição Y atual.
     * @param  string $direction Direção: 'up' | 'down' | 'left' | 'right'
     * @return array{int, int, string} [novoX, novoY, direçãoVisual]
     */
    public function calculateTarget(int $x, int $y, string $direction): array
    {
        return match ($direction) {
            'up'    => [$x,     $y - 1, 'back'],
            'down'  => [$x,     $y + 1, 'front'],
            'left'  => [$x - 1, $y,     'front-left'],
            'right' => [$x + 1, $y,     'front-right'],
            default => [$x,     $y,     'front'],
        };
    }

    /**
     * Verifica se uma posição é válida para movimento.
     *
     * Uma posição é válida quando:
     * 1. Está dentro dos limites do grid.
     * 2. O tile correspondente é do tipo 'caminho'.
     *
     * @param  GameState $state Estado atual do jogo.
     * @param  int       $x     Posição X a verificar.
     * @param  int       $y     Posição Y a verificar.
     * @return bool
     */
    public function isValidPosition(GameState $state, int $x, int $y): bool
    {
        if ($x < 0 || $y < 0 || $x >= $state->gridSize || $y >= $state->gridSize) {
            return false;
        }

        return ($state->map[$y][$x] ?? '') === 'caminho';
    }

    /**
     * Retorna o caminho do asset (imagem) para um tipo de tile.
     *
     * Mapeia tipos de tiles para os novos assets de ambiente urbano pos-apocaliptico.
     *
     * @param  string $type Tipo do tile: 'caminho' | 'grama' | 'concreto' | 'asfalto' | 'terra'
     * @return string Caminho relativo ao diretorio public/.
     */
    public function getTileAsset(string $type, string $atmosphere = 'bairro_abandonado', array $neighbors = []): string
    {
        if ($type === 'caminho') {
            return 'mapas/tiles/asfalto_v2.png'; // Asset base único
        }

        // Mapeia tiles com suporte a atmosferas diferentes
        return match ($type) {
            'grama' => 'mapas/tiles/grama_morta.png',
            'parede' => 'mapas/tiles/parede_predio.png',
            'obstaculo' => 'mapas/tiles/obstaculo_caixas.png',
            'militar' => 'mapas/tiles/zona_militar.png',
            'laboratorio' => 'mapas/tiles/laboratorio_sujo.png',
            'concreto' => 'mapas/tiles/concreto.png',
            'asfalto'  => 'mapas/tiles/asfalto_v2.png',
            'terra'    => 'mapas/tiles/terra.png',
            'grade'    => 'mapas/tiles/concreto.png',
            default    => 'mapas/tiles/grama_morta.png',
        };
    }

    /**
     * Determina a rotação e o tipo de asset para o caminho.
     */
    public function getPathTileData(array $neighbors): array
    {
        $top = $neighbors['top'] ?? false;
        $bottom = $neighbors['bottom'] ?? false;
        $left = $neighbors['left'] ?? false;
        $right = $neighbors['right'] ?? false;

        // 1. Curvas (Usando o asset de curva padronizado)
        if ($top && $right && !$bottom && !$left) return ['asset' => 'mapas/tiles/asfalto_curva_tr.png', 'rotate' => 0];
        if ($top && $left && !$bottom && !$right) return ['asset' => 'mapas/tiles/asfalto_curva_tr.png', 'rotate' => 270];
        if ($bottom && $right && !$top && !$left) return ['asset' => 'mapas/tiles/asfalto_curva_tr.png', 'rotate' => 90];
        if ($bottom && $left && !$top && !$right) return ['asset' => 'mapas/tiles/asfalto_curva_tr.png', 'rotate' => 180];

        // 2. Retas Horizontais (Gira o asfalto vertical em 90 graus)
        if (($left || $right) && !$top && !$bottom) {
            return ['asset' => 'mapas/tiles/asfalto_v2.png', 'rotate' => 90];
        }

        // 3. Interseções (Fallback para horizontal)
        if ($left && $right) {
            return ['asset' => 'mapas/tiles/asfalto_v2.png', 'rotate' => 90];
        }

        // 4. Padrão: Vertical
        return ['asset' => 'mapas/tiles/asfalto_v2.png', 'rotate' => 0];
    }

    /**
     * Determina o asset de asfalto correto com base nos vizinhos (cima, baixo, esquerda, direita).
     */


    /**
     * Retorna o caminho do asset para um objeto de cenario.
     *
     * Objetos de cenario sao elementos decorativos/obstaculos no mapa.
     *
     * @param  string $objectType Tipo do objeto: 'carro' | 'barricada' | 'destrocos'
     * @return string Caminho relativo ao diretorio public/.
     */
    public function getSceneryAsset(string $objectType): string
    {
        return match ($objectType) {
            'carro'              => 'mapas/cenarios/carro_policia.png',
            'carro_destruido'    => 'mapas/cenarios/carro_destruido.png',
            'barricada'          => 'mapas/cenarios/barricada_militar.png',
            'barricada_original' => 'mapas/cenarios/barricada.png',
            'destrocos'          => 'mapas/cenarios/destrocos.png',
            'poste'              => 'mapas/cenarios/poste_luz.png',
            'radioactive_barrel' => 'mapas/cenarios/radioactive_barrel.png',
            'cerca'              => 'mapas/cenarios/cerca_quebrada.png',
            'arvore_seca'        => 'mapas/cenarios/arvore_seca.png',
            'placa_pare'         => 'mapas/cenarios/placa_pare.png',
            'caixas'             => 'mapas/cenarios/caixas_madeira.png',
            'lixo'               => 'mapas/cenarios/lixo_urbano.png',
            'sangue'             => 'efeitos/sangue_v2.png',
            'sangue_original'    => 'efeitos/blood_splatter.png',
            'fumaca'             => 'efeitos/smoke_effect.png',
            default              => 'mapas/cenarios/destrocos.png',
        };
    }

    /**
     * Retorna a configuração de assets baseada na atmosfera da fase.
     *
     * @param  string $atmosphere Nome da atmosfera.
     * @return array<string, string>
     */
    public function getAtmosphereConfig(string $atmosphere): array
    {
        return match ($atmosphere) {
            'bairro_abandonado' => [
                'tile_caminho' => 'mapas/tiles/asfalto.png',
                'tile_grama'   => 'mapas/tiles/grama_degradada.png',
                'overlay'      => 'rgba(0, 0, 0, 0.2)',
            ],
            'rua_bloqueada' => [
                'tile_caminho' => 'mapas/tiles/concreto.png',
                'tile_grama'   => 'mapas/tiles/terra.png',
                'overlay'      => 'rgba(50, 0, 0, 0.1)',
            ],
            'zona_contaminada' => [
                'tile_caminho' => 'mapas/tiles/asfalto.png',
                'tile_grama'   => 'mapas/tiles/terra.png',
                'overlay'      => 'rgba(0, 50, 0, 0.2)',
            ],
            default => [
                'tile_caminho' => 'mapas/tiles/asfalto.png',
                'tile_grama'   => 'mapas/tiles/grama_degradada.png',
                'overlay'      => 'transparent',
            ],
        };
    }
}
