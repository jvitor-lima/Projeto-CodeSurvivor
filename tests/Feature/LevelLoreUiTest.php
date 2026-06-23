<?php

namespace Tests\Feature;

use Tests\TestCase;

class LevelLoreUiTest extends TestCase
{
    public function test_level_five_renders_lore_tabs_when_unlocked(): void
    {
        $this->withSession([
            'level_progress' => [
                '1' => 'completed',
                '2' => 'completed',
                '3' => 'completed',
                '4' => 'completed',
                '5' => 'available',
            ],
        ])
            ->get('/game?level=5')
            ->assertOk()
            ->assertSee('Historia da fase')
            ->assertSee('Zealot')
            ->assertSee('Ganado')
            ->assertSee('Visual')
            ->assertSee('zombie/zealots/zealot_idle.png');
    }

    public function test_level_two_does_not_render_lore_action(): void
    {
        $this->withSession(['level_progress' => ['1' => 'completed', '2' => 'available']])
            ->get('/game?level=2')
            ->assertOk()
            ->assertDontSee('Historia da fase');
    }
}
