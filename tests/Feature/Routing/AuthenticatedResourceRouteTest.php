<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Route;

test('guest trying to access tasks is redirected to login', function () {
    $response = $this->get('/tasks');
    $response->assertRedirect('/login');
});

test('authenticated user can access tasks', function () {
    $user = User::factory()->create();

    // Creating a task so index doesn't just return empty, but status 200 is what we check
    $response = $this->actingAs($user)->get('/tasks');
    $response->assertStatus(200);
});

test('guest trying to access users is redirected to login', function () {
    $response = $this->get('/users');
    $response->assertRedirect('/login');
});

test('normal user trying to access users receives 403', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get('/users');
    $response->assertStatus(403);
});

test('super admin can access users', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/users');
    $response->assertStatus(200);
});

test('all seven task routes are protected by auth middleware', function () {
    $expected = [
        'tasks.index',
        'tasks.create',
        'tasks.store',
        'tasks.show',
        'tasks.edit',
        'tasks.update',
        'tasks.destroy',
    ];

    foreach ($expected as $name) {
        $route = Route::getRoutes()->getByName($name);
        expect($route->gatherMiddleware())->toContain('auth');
        expect($route->gatherMiddleware())->not->toContain('super_admin');
    }
});

test('all seven user routes are protected by auth and super_admin middleware in correct order', function () {
    $expected = [
        'users.index',
        'users.create',
        'users.store',
        'users.show',
        'users.edit',
        'users.update',
        'users.destroy',
    ];

    foreach ($expected as $name) {
        $route = Route::getRoutes()->getByName($name);

        $middlewares = $route->gatherMiddleware();
        expect($middlewares)->toContain('auth');
        expect($middlewares)->toContain('super_admin');

        // Find indices
        $authIndex = array_search('auth', $middlewares);
        $superAdminIndex = array_search('super_admin', $middlewares);

        expect($authIndex)->toBeLessThan($superAdminIndex);
    }
});
