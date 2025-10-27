<?php
Route::get('/players/{match_id}', [App\Http\Controllers\HomeController::class, 'getPlayersByMatch']);
Route::POST('/add-combination/{id}', [App\Http\Controllers\HomeController::class, 'addCombination']);
Route::POST('/players/{id}/update-created', [App\Http\Controllers\HomeController::class, 'updateIsCreated']);