<?php

namespace Tests\Feature;

use App\Game\Engine\GameEngine;
use App\Game\Engine\GameState;
use App\Livewire\GameBoard;
use Tests\TestCase;

class CampaignMapTwoTest extends TestCase
{
    public function test_map_two_is_locked_before_initial_map_is_completed(): void
    {
        $this->get('/map?map=2')
            ->assertOk()
            ->assertSee('Mapa Inicial')
            ->assertSee('Zona de Quarentena')
            ->assertSee('Corredor de Repeticao')
            ->assertDontSee('Arsenal da Quarentena');
    }

    public function test_map_two_unlocks_after_level_six_and_shows_phase_one(): void
    {
        $this->withSession([
            'level_progress' => [
                '1' => 'completed',
                '2' => 'completed',
                '3' => 'completed',
                '4' => 'completed',
                '5' => 'completed',
                '6' => 'completed',
                '7' => 'available',
            ],
        ])
            ->get('/map?map=2')
            ->assertOk()
            ->assertSee('Zona de Quarentena')
            ->assertSee('mapas/backgrounds/map2_quarantine_zone.png')
            ->assertSee('Arsenal da Quarentena')
            ->assertSee('FASE #01')
            ->assertDontSee('Corredor de Repeticao');
    }

    public function test_level_seven_renders_handgun_collectible_when_unlocked(): void
    {
        $this->withSession([
            'level_progress' => [
                '1' => 'completed',
                '2' => 'completed',
                '3' => 'completed',
                '4' => 'completed',
                '5' => 'completed',
                '6' => 'completed',
                '7' => 'available',
            ],
        ])
            ->get('/game?level=7')
            ->assertOk()
            ->assertSee('Arsenal da Quarentena')
            ->assertSee('Handgun')
            ->assertSee('itens/Handgun.png')
            ->assertSee('mapas/icones/mapa2_armory_icon.png')
            ->assertSee('level7_fase.png');
    }

    public function test_level_six_board_does_not_show_map_two_level_selector(): void
    {
        $this->withSession([
            'level_progress' => [
                '1' => 'completed',
                '2' => 'completed',
                '3' => 'completed',
                '4' => 'completed',
                '5' => 'completed',
                '6' => 'available',
                '7' => 'locked',
            ],
        ])
            ->get('/game?level=6')
            ->assertOk()
            ->assertSee('loadLevel(6)', false)
            ->assertDontSee('loadLevel(7)', false);
    }

    public function test_next_from_level_six_redirects_to_unlocked_map_two(): void
    {
        session([
            'level_progress' => [
                '1' => 'completed',
                '2' => 'completed',
                '3' => 'completed',
                '4' => 'completed',
                '5' => 'completed',
                '6' => 'available',
                '7' => 'locked',
            ],
        ]);

        $state = new GameState();
        (new GameEngine())->initLevel($state, 6);
        $state->win = true;

        $component = new GameBoard();
        $component->gameState = $state->toArray();

        $response = $component->nextLevel();

        $this->assertSame('http://localhost/map?map=2', $response->getTargetUrl());
        $this->assertSame('completed', session('level_progress')['6']);
        $this->assertSame('available', session('level_progress')['7']);
    }
}
