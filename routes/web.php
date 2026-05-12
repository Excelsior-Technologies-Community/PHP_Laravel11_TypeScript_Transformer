<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TypeScriptController;

Route::get('/', [TypeScriptController::class, 'index']);

Route::post('/generate', [TypeScriptController::class, 'generate'])
    ->name('generate');

Route::get('/download', [TypeScriptController::class, 'download'])
    ->name('download');