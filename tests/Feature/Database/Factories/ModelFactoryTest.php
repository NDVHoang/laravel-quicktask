<?php

use App\Models\Scopes\ActiveUserScope;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('user factory make creates model in memory but not saved', function () {
    $user = User::factory()->make();

    expect($user->exists)->toBeFalse()
        ->and($user->id)->toBeNull();
});

test('user factory creates active regular user', function () {
    $user = User::factory()->create();
    $user->refresh();

    expect($user->exists)->toBeTrue()
        ->and($user->id)->not->toBeNull()
        ->and($user->role)->toBe('user')
        ->and($user->is_active)->toEqual(true);
});

test('factory creates unique emails when creating multiple users', function () {
    $users = User::factory()->count(5)->create();

    $emails = $users->pluck('email')->unique();
    expect($emails->count())->toBe(5);
});

test('password do factory tạo là hash hợp lệ sau khi lưu', function () {
    $user = User::factory()->create();

    $storedPassword = $user->getRawOriginal('password');
    expect(Hash::check('password', $storedPassword))->toBeTrue();
});

test('state admin creates user with role admin', function () {
    $user = User::factory()->admin()->create();
    $user->refresh();

    expect($user->role)->toBe('admin');
});

test('state inactive creates user inactive', function () {
    $user = User::factory()->inactive()->create();
    $user->refresh();

    expect($user->is_active)->toEqual(false);
});

test('inactive user exists in database but hidden by global scope', function () {
    $user = User::factory()->inactive()->create();

    $found = User::find($user->id);
    expect($found)->toBeNull();

    $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
});

test('can find inactive user without global scope', function () {
    $user = User::factory()->inactive()->create();

    $found = User::withoutGlobalScope(ActiveUserScope::class)->find($user->id);

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($user->id);
});

test('task factory creates valid task and associated user', function () {
    $task = Task::factory()->create();

    expect($task->id)->not->toBeNull()
        ->and($task->user_id)->not->toBeNull()
        ->and($task->user)->toBeInstanceOf(User::class);
});

test('task factory for user uses provided user', function () {
    $user = User::factory()->create();
    $task = Task::factory()->for($user)->create();

    expect($task->user_id)->toBe($user->id);
});

test('can create user with multiple tasks via relationship factory', function () {
    $user = User::factory()
        ->has(Task::factory()->count(3))
        ->create();

    expect($user->tasks)->toHaveCount(3);

    foreach ($user->tasks as $task) {
        expect($task->user_id)->toBe($user->id);
    }
});
