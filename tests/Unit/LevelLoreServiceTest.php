<?php

namespace Tests\Unit;

use App\Game\Services\LoreService;
use Tests\TestCase;

class LevelLoreServiceTest extends TestCase
{
    public function test_it_loads_configured_lore_entries_for_supported_levels(): void
    {
        $service = new LoreService();

        $this->assertSame('Leon', $service->entriesForLevel(1)[0]['title']);
        $this->assertSame('Don Jose', $service->entriesForLevel(3)[0]['title']);
        $this->assertSame('Garrador', $service->entriesForLevel(4)[0]['title']);
        $this->assertSame(['Zealot', 'Ganado'], array_column($service->entriesForLevel(5), 'title'));
        $this->assertNotEmpty($service->entriesForLevel(5)[0]['images']);
        $this->assertSame('zombie/zealots/zealot_idle.png', $service->entriesForLevel(5)[0]['images'][0]['path']);
    }

    public function test_it_hides_lore_for_levels_without_configured_files(): void
    {
        $this->assertSame([], (new LoreService())->entriesForLevel(2));
    }
}
