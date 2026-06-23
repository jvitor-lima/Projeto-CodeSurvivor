<?php

namespace App\Game\Systems;

use App\Game\Config\ItemConfig;
use App\Game\Engine\GameState;

class CollectibleSystem
{
    public function collectAtPlayer(GameState $state): ?array
    {
        foreach ($state->collectibles as $index => $collectible) {
            if (($collectible['x'] ?? null) !== $state->player->x || ($collectible['y'] ?? null) !== $state->player->y) {
                continue;
            }

            $itemId = (string) ($collectible['itemId'] ?? $collectible['id'] ?? '');
            $item = ItemConfig::collected($itemId);

            if ($item === null) {
                continue;
            }

            $state->player->addItem($item);

            if ($item['equipped'] ?? false) {
                $state->player->equipItem($itemId);
            }

            unset($state->collectibles[$index]);
            $state->collectibles = array_values($state->collectibles);
            $state->addLog("ITEM COLETADO: Leon pegou {$item['name']}.");

            return $item;
        }

        return null;
    }
}
