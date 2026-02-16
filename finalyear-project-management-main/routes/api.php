<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/projects/{id}/analyze', [\App\Http\Controllers\Api\ProjectHealthController::class, 'analyze']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/projects', [\App\Http\Controllers\Api\ProjectController::class, 'index']);
    Route::post('/projects', [\App\Http\Controllers\Api\ProjectController::class, 'store']);
    Route::get('/projects/{id}', [\App\Http\Controllers\Api\ProjectController::class, 'show']);

    Route::get('/projects/{projectId}/tickets', [\App\Http\Controllers\Api\TicketController::class, 'index']);
    Route::post('/tickets', [\App\Http\Controllers\Api\TicketController::class, 'store']);
    Route::put('/tickets/{id}', [\App\Http\Controllers\Api\TicketController::class, 'update']);
});
