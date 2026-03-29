<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\ColumnController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\InfluencerController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TaskCardController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn() => response()->json(['status' => 'ok']));

// Public auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Boards (Tasks / Kanban)
    Route::apiResource('boards', BoardController::class);
    Route::post('boards/{board}/columns', [ColumnController::class, 'store']);
    Route::put('boards/{board}/columns/reorder', [ColumnController::class, 'reorder']);
    Route::put('columns/{column}', [ColumnController::class, 'update']);
    Route::delete('columns/{column}', [ColumnController::class, 'destroy']);

    // Task Cards
    Route::post('columns/{column}/cards', [TaskCardController::class, 'store']);
    Route::get('cards/{taskCard}', [TaskCardController::class, 'show']);
    Route::put('cards/{taskCard}', [TaskCardController::class, 'update']);
    Route::delete('cards/{taskCard}', [TaskCardController::class, 'destroy']);
    Route::post('cards/{taskCard}/move', [TaskCardController::class, 'move']);
    Route::post('cards/{taskCard}/attachments', [TaskCardController::class, 'uploadAttachment']);

    // Marketing — Influencers
    Route::get('influencers/stats', [InfluencerController::class, 'stats']);
    Route::apiResource('influencers', InfluencerController::class);

    // Reports
    Route::get('reports', [ReportController::class, 'index']);
    Route::get('reports/{game}', [ReportController::class, 'show']);

    // Games
    Route::get('games/steam-search', [GameController::class, 'steamSearch']);
    Route::post('games/{game}/refresh-steam', [GameController::class, 'refreshSteamData']);
    Route::apiResource('games', GameController::class);
});
