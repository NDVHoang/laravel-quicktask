<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected to login when accessing dashboard', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('authenticated user can access dashboard and it renders correctly', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
    );
});
