<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;
use function Pest\Laravel\withSession;

beforeEach(function () {
    Route::get('/_test-locale', function () {
        return App::getLocale();
    })->middleware('web');
});

test('no locale in session uses default locale', function () {
    get('/_test-locale')->assertSee('en');
});

test('session locale=en sets locale en', function () {
    withSession(['locale' => 'en'])
        ->get('/_test-locale')
        ->assertSee('en');
});

test('session locale=vi sets locale vi', function () {
    withSession(['locale' => 'vi'])
        ->get('/_test-locale')
        ->assertSee('vi');
});

test('invalid session locale falls back safely', function () {
    withSession(['locale' => 'invalid-locale'])
        ->get('/_test-locale')
        ->assertSee('en');
});

test('middleware runs on login and register routes', function () {
    withSession(['locale' => 'vi'])->get('/login');
    expect(App::getLocale())->toBe('vi');

    withSession(['locale' => 'vi'])->get('/register');
    expect(App::getLocale())->toBe('vi');
});

test('middleware does not query database', function () {
    DB::enableQueryLog();
    withSession(['locale' => 'vi'])->get('/_test-locale');
    expect(DB::getQueryLog())->toBeEmpty();
});
