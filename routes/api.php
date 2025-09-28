<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\FileUploadController;
use Illuminate\Support\Facades\Route;

Route::post('tasks/bulk', [TaskController::class, 'bulkStore']); 
Route::post('upload/chunk', [FileUploadController::class, 'uploadChunk']);
Route::post('upload/combine', [FileUploadController::class, 'combineChunks']);
Route::resource('tasks', TaskController::class)->only([
    'index', 'store', 'show', 'update', 'destroy'
]);

