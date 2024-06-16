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



    public function generateHeroesPost(Request $request)
    {
        return $this->generateHeroes($request->input('num'));
    }


    public function generateHeroesGet($num)
    {
        return $this->generateHeroes($num);
    }



    public function generateHeroes($numHeroes)
    {
        if ($numHeroes === null || $numHeroes <= 0) {
            return response()->json(['error' => 'Number of heroes is required and must be greater than zero'], 400);
        }

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

            $round[self::ATTACKER] = $attacker->type;
            $round[self::DEFENDER] = $defender->type;
            $round[self::RESULT] = 'nothing';


            // Because the variations num low, I choose else. Otherwise I choose Switch
            if ($attacker->type == Hero::TYPE_ARCHER) {
                if ($defender->type == Hero::TYPE_CHILVARY) {
                    // if Attacker is ARCHER, and Defender is Chilvary, the defender 40% chance to DIE
                    if (rand(0, 100) < 40) {
                        $round[self::RESULT] = self::RESULT_DEFENDER_DIES;
                        $defender->health = 0;
                    }
                } elseif ($defender->type == Hero::TYPE_SWORDSMAN || $defender->type == Hero::TYPE_ARCHER) {
                    $round[self::RESULT] = self::RESULT_DEFENDER_DIES;
                    $defender->health = 0;
                }

                // switch case
                // switch ($defender->type)
                // {
                //     case Hero::TYPE_CHILVARY :
                //         if (rand(0, 100) < 40) {
                //             $round[self::RESULT] = self::RESULT_DEFENDER_DIES;
                //             $defender->health = 0;
                //         }
                //         break;
                //     // if defender is swordsman or archer
                //     default:
                //         $round[self::RESULT] = self::RESULT_DEFENDER_DIES;
                //         $defender->health = 0;
                //         break;
                //     }


            } elseif ($attacker->type == Hero::TYPE_SWORDSMAN) {
                if ($defender->type == Hero::TYPE_CHILVARY) {
                    $round[self::RESULT] = 'nothing';
                } elseif ($defender->type == Hero::TYPE_SWORDSMAN || $defender->type == Hero::TYPE_ARCHER) {
                    $round[self::RESULT] = self::RESULT_DEFENDER_DIES;
                    $defender->health = 0;
                }
            } elseif ($attacker->type == Hero::TYPE_CHILVARY) {
                if ($defender->type == Hero::TYPE_CHILVARY || $defender->type == Hero::TYPE_SWORDSMAN || $defender->type == Hero::TYPE_ARCHER) {
                    $round[self::RESULT] = self::RESULT_DEFENDER_DIES;
                    $defender->health = 0;
                }
            }

            if ($round[self::RESULT] == self::RESULT_DEFENDER_DIES) {
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
