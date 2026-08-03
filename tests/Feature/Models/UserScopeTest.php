<?php

use App\Models\Scopes\ActiveUserScope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Helper to setup users for tests without causing IDE undefined property warnings on $this.
 *
 * @return array{0: User, 1: User, 2: User, 3: User}
 */
function setupTestUsers(): array
{
    // Active admin
    $activeAdmin = User::factory()->create([
        'email' => 'active-admin@example.com',
    ]);
    $activeAdmin->role = 'admin';
    $activeAdmin->is_active = true;
    $activeAdmin->save();

    // Active regular user
    $activeUser = User::factory()->create([
        'email' => 'active-user@example.com',
    ]);
    $activeUser->role = 'user';
    $activeUser->is_active = true;
    $activeUser->save();

    // Inactive admin
    $inactiveAdmin = User::factory()->create([
        'email' => 'inactive-admin@example.com',
    ]);
    $inactiveAdmin->role = 'admin';
    $inactiveAdmin->is_active = false;
    $inactiveAdmin->save();

    // Inactive regular user
    $inactiveUser = User::factory()->create([
        'email' => 'inactive-user@example.com',
    ]);
    $inactiveUser->role = 'user';
    $inactiveUser->is_active = false;
    $inactiveUser->save();

    return [$activeAdmin, $activeUser, $inactiveAdmin, $inactiveUser];
}

test('User::query()->get() only returns active users', function () {
    [$activeAdmin, $activeUser, $inactiveAdmin, $inactiveUser] = setupTestUsers();

    $users = User::query()->get();

    expect($users)->toHaveCount(2)
        ->and($users->contains($activeAdmin))->toBeTrue()
        ->and($users->contains($activeUser))->toBeTrue()
        ->and($users->contains($inactiveAdmin))->toBeFalse()
        ->and($users->contains($inactiveUser))->toBeFalse();
});

test('User::find() returns null for inactive user due to global scope', function () {
    [$activeAdmin, $activeUser, $inactiveAdmin, $inactiveUser] = setupTestUsers();

    $found = User::find($inactiveUser->id);
    expect($found)->toBeNull();
});

test('User::admin()->get() only returns active admins', function () {
    [$activeAdmin, $activeUser, $inactiveAdmin, $inactiveUser] = setupTestUsers();

    $admins = User::admin()->get();

    expect($admins)->toHaveCount(1)
        ->and($admins->first()->is($activeAdmin))->toBeTrue();
});

test('active regular user does not appear in admin scope', function () {
    [$activeAdmin, $activeUser, $inactiveAdmin, $inactiveUser] = setupTestUsers();

    $admins = User::admin()->get();
    expect($admins->contains($activeUser))->toBeFalse();
});

test('inactive admin does not appear in admin scope when global scope applies', function () {
    [$activeAdmin, $activeUser, $inactiveAdmin, $inactiveUser] = setupTestUsers();

    $admins = User::admin()->get();
    expect($admins->contains($inactiveAdmin))->toBeFalse();
});

test('can bypass global scope to get all active and inactive users', function () {
    [$activeAdmin, $activeUser, $inactiveAdmin, $inactiveUser] = setupTestUsers();

    $users = User::withoutGlobalScope(ActiveUserScope::class)->get();

    expect($users)->toHaveCount(4)
        ->and($users->contains($activeAdmin))->toBeTrue()
        ->and($users->contains($activeUser))->toBeTrue()
        ->and($users->contains($inactiveAdmin))->toBeTrue()
        ->and($users->contains($inactiveUser))->toBeTrue();
});

test('can bypass global scope to get all active and inactive admins', function () {
    [$activeAdmin, $activeUser, $inactiveAdmin, $inactiveUser] = setupTestUsers();

    $admins = User::withoutGlobalScope(ActiveUserScope::class)->admin()->get();

    expect($admins)->toHaveCount(2)
        ->and($admins->contains($activeAdmin))->toBeTrue()
        ->and($admins->contains($inactiveAdmin))->toBeTrue()
        ->and($admins->contains($activeUser))->toBeFalse()
        ->and($admins->contains($inactiveUser))->toBeFalse();
});

test('scope only filters data when querying and does not modify or delete database records', function () {
    [$activeAdmin, $activeUser, $inactiveAdmin, $inactiveUser] = setupTestUsers();

    // Assert all 4 users still exist in the DB despite global scopes
    $this->assertDatabaseHas('users', ['id' => $activeAdmin->id, 'is_active' => true]);
    $this->assertDatabaseHas('users', ['id' => $activeUser->id, 'is_active' => true]);
    $this->assertDatabaseHas('users', ['id' => $inactiveAdmin->id, 'is_active' => false]);
    $this->assertDatabaseHas('users', ['id' => $inactiveUser->id, 'is_active' => false]);
});
