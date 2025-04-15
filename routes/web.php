<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodoController;
use App\Http\Middleware\CheckSessionActivity;
use Illuminate\Support\Facades\Session;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::middleware([CheckSessionActivity::class])->group(function () {
    Route::get('/', [TodoController::class, 'index'])->name('todos.index');
    Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
    Route::delete('/todos/{id}', [TodoController::class, 'destroy'])->name('todos.destroy');
    Route::post('/nuclear-reset', [TodoController::class, 'nuclearReset']); // For tab closing
});
