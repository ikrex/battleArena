<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Arena;
use App\Models\Hero;

class ArenaController extends Controller
{

    const ATTACKER = "attacker";
    const DEFENDER = "defender";
    const RESULT = "result";
    const RESULT_DEFENDER_DIES = "defender dies";


    public function generateHeroes($numHeroes)
    {

        $arena = Arena::create();

        $types = [Hero::TYPE_ARCHER, Hero::TYPE_CHILVARY, Hero::TYPE_SWORDSMAN];
        $healths = [
            Hero::TYPE_ARCHER => 100,
            Hero::TYPE_CHILVARY => 150,
            Hero::TYPE_SWORDSMAN => 120
        ];

        for ($i = 0; $i < $numHeroes; $i++) {
            $type = $types[array_rand($types)];
            Hero::create([
                Hero::TYPE => $type,
                Hero::HELATH => $healths[$type],
                Hero::ARENA_ID => $arena->id
            ]);
        }

        return response()->json([Hero::ARENA_ID => $arena->id]);
    }

    public function battle($arenaId)
    {
        $arena = Arena::findOrFail($arenaId);
        $history = [];

        while ($arena->heroes()->count() > 1) {
            $round = [];
            $heroes = $arena->heroes()->inRandomOrder()->get();

            $attacker = $heroes->shift();
            $defender = $heroes->shift();

            $round['attacker'] = $attacker->type;
            $round['defender'] = $defender->type;
            $round['result'] = 'nothing';

            if ($attacker->type == Hero::TYPE_ARCHER) {
                if ($defender->type == Hero::TYPE_CHILVARY) {
                    if (rand(0, 100) < 40) {
                        $round['result'] = self::RESULT_DEFENDER_DIES;
                        $defender->health = 0;
                    }
                } elseif ($defender->type == Hero::TYPE_SWORDSMAN || $defender->type == Hero::TYPE_ARCHER) {
                    $round['result'] = self::RESULT_DEFENDER_DIES;
                    $defender->health = 0;
                }
            } elseif ($attacker->type == Hero::TYPE_SWORDSMAN) {
                if ($defender->type == Hero::TYPE_CHILVARY) {
                    $round['result'] = 'nothing';
                } elseif ($defender->type == Hero::TYPE_SWORDSMAN || $defender->type == Hero::TYPE_ARCHER) {
                    $round['result'] = self::RESULT_DEFENDER_DIES;
                    $defender->health = 0;
                }
            } elseif ($attacker->type == Hero::TYPE_CHILVARY) {
                if ($defender->type == Hero::TYPE_CHILVARY || $defender->type == Hero::TYPE_SWORDSMAN || $defender->type == Hero::TYPE_ARCHER) {
                    $round['result'] = self::RESULT_DEFENDER_DIES;
                    $defender->health = 0;
                }
            }

            if ($round['result'] == self::RESULT_DEFENDER_DIES) {
                $defender->save();
            }

            $attacker->health /= 2;
            if ($attacker->health < $attacker->getOriginal('health') / 4) {
                $attacker->health = 0;
            }
            $attacker->save();

            foreach ($arena->heroes as $hero) {
                if ($hero->health > 0 && $hero->id != $attacker->id && $hero->id != $defender->id) {
                    $hero->health = min($hero->health + 10, $hero->getOriginal('health'));
                    $hero->save();
                }
            }

            $round['attacker_health'] = $attacker->health;
            $round['defender_health'] = $defender->health;
            $round['remaining_heroes'] = $arena->heroes()->where('health', '>', 0)->count();

            $history[] = $round;

            $arena->heroes()->where('health', 0)->delete();
        }

        return response()->json(['history' => $history]);
    }
}
