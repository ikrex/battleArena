<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ArenaController;

Route::get('/', function () {
    return view('welcome');
});


Route::post('/generateHeroes', [ArenaController::class, 'generateHeroesPost']);
Route::get('/generateHeroes/{num}', [ArenaController::class, 'generateHeroesGet']);
Route::get('/battle/{arenaId}', [ArenaController::class, 'battle']);
