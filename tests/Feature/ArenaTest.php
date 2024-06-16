<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Arena;
use App\Models\Hero;


class ArenaTest extends TestCase
{


    const MIN_HEROES_IN_ARENA = 2;
    const MAX_HEROES_IN_ARENA = 7;

    // use RefreshDatabase;

    public function testGenerateHeroes()
    {
        $response = $this->postJson('/generateHeroes', ['num' => rand(self::MIN_HEROES_IN_ARENA, self::MAX_HEROES_IN_ARENA)]);
        $response->assertStatus(200)->assertJsonStructure(['arena_id']);
    }

    public function testBattle()
    {
        $response = $this->postJson('/generateHeroes', ['num' => rand(self::MIN_HEROES_IN_ARENA, self::MAX_HEROES_IN_ARENA)]);
        $response->assertStatus(200);
        $arenaId = $response->json('arena_id');

        $response = $this->getJson("/battle/{$arenaId}");
        $response->assertStatus(200)->assertJsonStructure(['history']);
    }




    /**
     * A basic feature test example.
     */
    // public function test_example(): void
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }
}
