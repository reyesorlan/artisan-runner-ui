<?php

use CodeSquirrel\ArtisanRunnerUI\Http\Controllers\ArtisanRunnerUIController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ArtisanRunnerUIController::class, 'index'])->name('artisan-runner-ui.index');
Route::post('/run', [ArtisanRunnerUIController::class, 'run'])->name('artisan-runner-ui.run');