<?php

namespace Tests\Feature;

use Tests\TestCase;

class LevelSixUiTest extends TestCase
{
    public function test_level_six_renders_when_unlocked_with_repeat_available(): void
    {
        $this->withSession([
            'level_progress' => [
                '1' => 'completed',
                '2' => 'completed',
                '3' => 'completed',
                '4' => 'completed',
                '5' => 'completed',
                '6' => 'available',
            ],
        ])
            ->get('/game?level=6')
            ->assertOk()
            ->assertSee('Corredor de Repeticao')
            ->assertSee('Use repeat para atravessar a trilha repetindo o mesmo padrao')
            ->assertSee('repeat(2)')
            ->assertSee('Ciclo Repeat')
            ->assertSee('grid-template-columns: repeat(10');
    }

    public function test_level_map_lists_level_six(): void
    {
        $this->get('/map')
            ->assertOk()
            ->assertSee('Corredor de Repeticao')
            ->assertSee('FASE #06');
    }
}
