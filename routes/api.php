<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);

Route::get('menus', [MenuController::class, 'index']);
Route::get('menus/{id}', [MenuController::class, 'show']);
Route::post('menus', [MenuController::class, 'store']);
Route::put('menus/{id}', [MenuController::class, 'update']);
Route::delete('menus/{id}', [MenuController::class, 'destroy']);

Route::get('promos', [PromoController::class, 'index']);
Route::get('promos/{id}', [PromoController::class, 'show']);
Route::post('promos', [PromoController::class, 'store']);
Route::put('promos/{id}', [PromoController::class, 'update']);
Route::delete('promos/{id}', [PromoController::class, 'destroy']);

Route::get('transactions', [TransactionController::class, 'index']);
Route::get('transactions/{id}', [TransactionController::class, 'show']); 
Route::post('transactions/create', [TransactionController::class, 'store']); 
Route::put('transactions/{id}', [TransactionController::class, 'update']); 
Route::delete('transactions/{id}', [TransactionController::class, 'destroy']);

Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);