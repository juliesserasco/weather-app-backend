<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Weather\WeatherController;

Route::get('/get-weather', [WeatherController::class, 'index'])->name('weather.index');