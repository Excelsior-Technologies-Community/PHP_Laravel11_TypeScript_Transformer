<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TypeScriptController;

Route::get('/', [TypeScriptController::class, 'index'])->name('dashboard');

Route::post('/generate', [TypeScriptController::class, 'generate'])
    ->middleware('throttle:5,1')
    ->name('generate');

Route::get('/download', [TypeScriptController::class, 'download'])->name('download');
Route::get('/stats', [TypeScriptController::class, 'stats'])->name('stats');
Route::get('/history', [TypeScriptController::class, 'history'])->name('history');