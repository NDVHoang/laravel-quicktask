<?php

use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('guest can post to switch to vi', function () {
    post(route('locale.update', ['locale' => 'vi']))
        ->assertSessionHas('locale', 'vi')
        ->assertRedirect();
});

test('guest can post to switch to en', function () {
    post(route('locale.update', ['locale' => 'en']))
        ->assertSessionHas('locale', 'en')
        ->assertRedirect();
});

test('unsupported locale is rejected', function () {
    post(route('locale.update', ['locale' => 'fr']))
        ->assertStatus(400);
});

test('get to locale route does not work', function () {
    get(route('locale.update', ['locale' => 'vi']))
        ->assertStatus(405); // Method not allowed
});

test('route has web middleware which includes csrf', function () {
    $route = collect(Route::getRoutes()->getRoutes())->first(function ($route) {
        return $route->getName() === 'locale.update';
    });

    expect($route->gatherMiddleware())->toContain('web');
});

test('route does not require auth', function () {
    $route = collect(Route::getRoutes()->getRoutes())->first(function ($route) {
        return $route->getName() === 'locale.update';
    });

    expect($route->gatherMiddleware())->not->toContain('auth');
});

test('route does not require super_admin', function () {
    $route = collect(Route::getRoutes()->getRoutes())->first(function ($route) {
        return $route->getName() === 'locale.update';
    });

    expect($route->gatherMiddleware())->not->toContain('super_admin');
});
