<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::resource('users', UserController::class);

Route::controller(TaskController::class)
    ->prefix('tasks')
    ->name('tasks.')
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{task}', 'show')->name('show');
        Route::get('/{task}/edit', 'edit')->name('edit');
        Route::match(['put', 'patch'], '/{task}', 'update')->name('update');
        Route::delete('/{task}', 'destroy')->name('destroy');
    });
