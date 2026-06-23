<?php

namespace Tests\Unit;

use App\Game\Config\ItemConfig;
use App\Game\Config\LevelConfig;
use App\Game\Engine\GameEngine;
use App\Game\Engine\GameState;
use PHPUnit\Framework\TestCase;

class LevelSevenHandgunTest extends TestCase
{
    public function test_level_seven_is_map_two_phase_one_with_handgun_collectible(): void
    {
        $levels = LevelConfig::getAllLevels();
        $config = LevelConfig::getLevelBySequence(7);

        $this->assertSame(2, $levels[7]['mapId']);
        $this->assertSame(1, $levels[7]['mapLevel']);
        $this->assertSame('Arsenal da Quarentena', $config['name']);
        $this->assertSame(['handgun'], $config['requiredItems']);
        $this->assertSame('mapa2_armory', $config['mapIcon']);
        $this->assertSame('handgun', $config['collectibles'][0]['itemId']);
        $this->assertSame('itens/Handgun.png', ItemConfig::get('handgun')['sprite']);
    }

    public function test_level_seven_collects_handgun_and_uses_pistol_sprite(): void
    {
        $state = new GameState();
        $engine = new GameEngine();
        $engine->initLevel($state, 7);

        $this->assertSame('personagem/person_pistola/backwards.png', $state->player->getIdleSprite());

        $queue = $engine->prepareCommands($state, $state->initialCode);
        $step = 0;
        $done = false;

        for ($guard = 0; ! $done && $guard < 40; $guard++) {
            $result = $engine->step($state, $queue, $step);
            $queue = $result['queue'];
            $step = $result['step'];
            $done = $result['done'];
        }

        $this->assertTrue($done, 'The command queue did not finish.');
        $this->assertTrue($state->win);
        $this->assertArrayHasKey('handgun', $state->player->inventory);
        $this->assertTrue($state->player->inventory['handgun']['equipped']);
        $this->assertSame('personagem/person_pistola/backwards.png', $state->player->getIdleSprite());
    }

    public function test_handgun_sprite_has_priority_over_staff_on_map_two(): void
    {
        $state = new GameState();
        $state->player->addItem([
            'id' => 'bastao',
            'name' => 'Bastao de Defesa',
            'type' => 'weapon',
            'sprite' => 'itens/bastao.png',
            'collected' => true,
            'equipped' => true,
        ]);
        $state->player->addItem([
            'id' => 'handgun',
            'name' => 'Handgun',
            'type' => 'weapon',
            'sprite' => 'itens/Handgun.png',
            'collected' => true,
            'equipped' => true,
        ]);

        (new GameEngine())->initLevel($state, 7);

        $this->assertSame('personagem/person_pistola/backwards.png', $state->player->getIdleSprite());
    }
}
