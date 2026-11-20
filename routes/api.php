<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\MetaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MetaController::class, 'root']);
Route::get('/health', [MetaController::class, 'health']);
Route::get('/config', [MetaController::class, 'config']);

Route::get('/items', [ItemController::class, 'index']);
Route::post('/items', [ItemController::class, 'store']);
Route::get('/items/{id}', [ItemController::class, 'show'])->whereNumber('id');
Route::patch('/items/{id}', [ItemController::class, 'update'])->whereNumber('id');
Route::delete('/items/{id}', [ItemController::class, 'destroy'])->whereNumber('id');
