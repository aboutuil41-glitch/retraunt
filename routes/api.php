<?php

use App\Http\Controllers\adminpanel;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\GeminiTestController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn(Request $r) => $r->user());
    Route::get('/me', [UserController::class, 'index']);
    Route::get('/profile', [UserController::class, 'DietaryProfile']);
    Route::put('/profile', [UserController::class, 'UpdateDietaryProfile']);

    Route::get('/recommendations',              [RecommendationController::class, 'index']);
    Route::get('/recommendations/{plate_id}',   [RecommendationController::class, 'show']);
    Route::post('/recommendations/analyze/{plate_id}', [RecommendationController::class, 'analyze']);

    // ── Admin only ──────────────────────────────────────────
    Route::middleware('is_admin')->group(function () {
        Route::get('/admin/stats', [adminpanel::class, 'status']);

        Route::apiResource('dishes',      DishController::class);
        Route::apiResource('categories',  CategoryController::class);
        Route::apiResource('ingredients', IngredientController::class);

        Route::get('/gemini-test',  [GeminiTestController::class, 'test']);
        Route::get('/list-models',  [GeminiTestController::class, 'listModels']);
    });

    // ── Public category browsing (all users) ─────────────────
    Route::get('categories',           [CategoryController::class, 'index']);
    Route::get('categories/{id}',      [CategoryController::class, 'show']);
    Route::get('categories/{id}/dish', [CategoryController::class, 'indexDishes']);
    Route::get('dishes',               [DishController::class, 'index']);
    Route::get('dishes/{dish}',        [DishController::class, 'show']);
});