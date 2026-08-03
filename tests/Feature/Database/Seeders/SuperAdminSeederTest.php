<?php

use App\Models\Scopes\ActiveUserScope;
use App\Models\User;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('quicktask.super_admin.name', 'Test Super Admin');
    Config::set('quicktask.super_admin.email', 'super-admin@example.test');
    Config::set('quicktask.super_admin.password', 'test-password-123'); // must be >= 12
});

test('seeder creates exactly one admin account', function () {
    $this->seed(SuperAdminSeeder::class);

    $admins = User::withoutGlobalScope(ActiveUserScope::class)->get();

    expect($admins)->toHaveCount(1)
        ->and($admins->first()->email)->toBe('super-admin@example.test');
});

test('admin account has valid role and is active', function () {
    $this->seed(SuperAdminSeeder::class);

    $admin = User::firstWhere('email', 'super-admin@example.test');

    expect($admin->role)->toBe('admin')
        ->and($admin->is_active)->toEqual(true);
});

test('password saved as hash and hash check passes', function () {
    $this->seed(SuperAdminSeeder::class);

    $admin = User::firstWhere('email', 'super-admin@example.test');
    $storedPassword = $admin->getRawOriginal('password');

    expect(Hash::check('test-password-123', $storedPassword))->toBeTrue();
});

test('running seeder twice does not duplicate records', function () {
    $this->seed(SuperAdminSeeder::class);
    $this->seed(SuperAdminSeeder::class);

    $admins = User::withoutGlobalScope(ActiveUserScope::class)->get();
    expect($admins)->toHaveCount(1);
});

test('running twice does not change saved password', function () {
    $this->seed(SuperAdminSeeder::class);

    $admin = User::firstWhere('email', 'super-admin@example.test');
    $admin->password = 'new-password-hash';
    $admin->save();

    $this->seed(SuperAdminSeeder::class);

    $updatedAdmin = User::firstWhere('email', 'super-admin@example.test');
    $storedPassword = $updatedAdmin->getRawOriginal('password');

    expect(Hash::check('new-password-hash', $storedPassword))->toBeTrue();
});

test('seeder can find and reactivate inactive user', function () {
    // Create inactive user with the super admin email
    User::factory()->inactive()->create([
        'email' => 'super-admin@example.test',
        'role' => 'user',
    ]);

    $this->seed(SuperAdminSeeder::class);

    $admin = User::withoutGlobalScope(ActiveUserScope::class)
        ->firstWhere('email', 'super-admin@example.test');

    expect($admin->is_active)->toEqual(true)
        ->and($admin->role)->toBe('admin');
});

test('missing config email throws exception', function () {
    Config::set('quicktask.super_admin.email', '');

    $this->expectException(InvalidArgumentException::class);
    $this->seed(SuperAdminSeeder::class);
});

test('missing config password throws exception', function () {
    Config::set('quicktask.super_admin.password', '');

    $this->expectException(InvalidArgumentException::class);
    $this->seed(SuperAdminSeeder::class);
});

test('password too short throws exception', function () {
    Config::set('quicktask.super_admin.password', 'short');

    $this->expectException(InvalidArgumentException::class);
    $this->seed(SuperAdminSeeder::class);
});
