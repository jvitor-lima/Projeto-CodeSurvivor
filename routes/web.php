<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\GameBoard;
use App\Livewire\LevelMap;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/map', LevelMap::class);
Route::get('/game', GameBoard::class);