<?php

namespace App\Game\Services;

use App\Game\Entities\Player;
use Throwable;

class InventoryService
{
    private const INVENTORY_SESSION_KEY = 'player_inventory';

    public function loadInventory(): array
    {
        if (! $this->canUseSession()) {
            return [];
        }

        $inventory = session(self::INVENTORY_SESSION_KEY, []);

        return is_array($inventory) ? $inventory : [];
    }

    public function saveInventory(array $inventory): void
    {
        if (! $this->canUseSession()) {
            return;
        }

        session([self::INVENTORY_SESSION_KEY => $this->normalize($inventory)]);
    }

    public function syncPlayer(Player $player): void
    {
        $player->inventory = $this->normalize($this->loadInventory());
    }

    public function persistPlayer(Player $player): void
    {
        $this->saveInventory($player->inventory);
    }

    public function hasItem(string $itemId): bool
    {
        $inventory = $this->loadInventory();

        return isset($inventory[$itemId]) && ($inventory[$itemId]['collected'] ?? false);
    }

    private function normalize(array $inventory): array
    {
        $normalized = [];

        foreach ($inventory as $key => $item) {
            if (! is_array($item)) {
                continue;
            }

            $id = (string) ($item['id'] ?? $key);
            $item['id'] = $id;
            $item['collected'] = $item['collected'] ?? true;
            $item['equipped'] = $item['equipped'] ?? false;
            $normalized[$id] = $item;
        }

        return $normalized;
    }

    private function canUseSession(): bool
    {
        try {
            return function_exists('app') && app()->bound('session');
        } catch (Throwable) {
            return false;
        }
    }
}
