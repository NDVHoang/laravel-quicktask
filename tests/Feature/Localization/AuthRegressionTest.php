<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

test('registration still creates normal active user', function () {
    post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->is_active)->toBeTruthy()
        ->and($user->is_super_admin)->toBeFalsy();
});

test('correct login still succeeds', function () {
    $user = User::factory()->create(['is_active' => true]);

    post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('logout still uses post', function () {
    $user = User::factory()->create();
    actingAs($user)->post('/logout')->assertRedirect('/');
    $this->assertGuest();
});

test('tasks routes require auth', function () {
    $route = collect(Route::getRoutes()->getRoutes())->first(fn ($r) => $r->getName() === 'tasks.index');
    expect($route->gatherMiddleware())->toContain('auth');
});

test('users routes require auth before super_admin', function () {
    $route = collect(Route::getRoutes()->getRoutes())->first(fn ($r) => $r->getName() === 'users.index');

    $middleware = $route->gatherMiddleware();
    expect($middleware)->toContain('auth')
        ->toContain('super_admin');

    $authIndex = array_search('auth', $middleware);
    $superAdminIndex = array_search('super_admin', $middleware);
    expect($authIndex)->toBeLessThan($superAdminIndex);
});

test('changing locale does not change user data', function () {
    $user = User::factory()->create(['name' => 'Original Name']);

    actingAs($user)
        ->post(route('locale.update', ['locale' => 'vi']))
        ->assertRedirect();

    $user->refresh();
    expect($user->name)->toBe('Original Name');
});
