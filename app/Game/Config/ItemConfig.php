<?php

namespace App\Game\Config;

class ItemConfig
{
    private const ITEMS = [
        'bastao' => [
            'id' => 'bastao',
            'name' => 'Bastao',
            'type' => 'weapon',
            'category' => 'weapon',
            'sprite' => 'itens/bastao.png',
            'collected' => false,
            'equipped' => false,
            'equipOnCollect' => true,
        ],
        'handgun' => [
            'id' => 'handgun',
            'name' => 'Pistola',
            'type' => 'weapon',
            'category' => 'weapon',
            'sprite' => 'itens/Handgun.png',
            'collected' => false,
            'equipped' => false,
            'equipOnCollect' => true,
        ],
    ];

    public static function get(string $id): ?array
    {
        return self::ITEMS[$id] ?? null;
    }

    public static function collected(string $id): ?array
    {
        $item = self::get($id);

        if ($item === null) {
            return null;
        }

        $item['collected'] = true;
        $item['equipped'] = (bool) ($item['equipOnCollect'] ?? false);

        return $item;
    }

    public static function all(): array
    {
        return self::ITEMS;
    }
}
