<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'cache.headers:no_store,no_cache,must_revalidate,max_age=0'])->name('dashboard');

Route::middleware(['auth', 'cache.headers:no_store,no_cache,must_revalidate,max_age=0'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('users', UserController::class)->middleware('super_admin');

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
});

Route::post('/locale/{locale}', LocaleController::class)->name('locale.update');

require __DIR__.'/auth.php';
