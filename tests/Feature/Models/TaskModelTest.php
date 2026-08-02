<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('users table has expected columns', function () {
    expect(Schema::hasColumns('users', [
        'id', 'name', 'email', 'password', 'role', 'is_active',
    ]))->toBeTrue();
});

test('tasks table has expected columns', function () {
    expect(Schema::hasColumns('tasks', [
        'id', 'user_id', 'title', 'description', 'status', 'due_date', 'created_at', 'updated_at',
    ]))->toBeTrue();
});

test('user model allows mass assignment for specific fields', function () {
    $user = new User;

    expect($user->getFillable())->toEqual(['name', 'email', 'password']);
});

test('user model does not allow mass assignment for role and is_active', function () {
    $user = new User;
    $user->fill([
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'secret',
        'role' => 'admin',
        'is_active' => false,
    ]);

    expect($user->getAttributes())->not->toHaveKey('role')
        ->and($user->getAttributes())->not->toHaveKey('is_active');
});

test('task model allows mass assignment for specific fields', function () {
    $task = new Task;

    expect($task->getFillable())->toEqual(['title', 'description', 'status', 'due_date']);
});

test('task model does not allow mass assignment for id and user_id', function () {
    $task = new Task;
    $task->fill([
        'id' => 999,
        'user_id' => 999,
        'title' => 'Test',
    ]);

    expect($task->getAttributes())->not->toHaveKey('id')
        ->and($task->getAttributes())->not->toHaveKey('user_id');
});

test('task is deleted when user is deleted (cascade delete)', function () {
    $user = User::factory()->create();

    $task = new Task;
    $task->title = 'Test Task';
    $task->user_id = $user->id;
    $task->save();

    expect(Task::where('id', $task->id)->exists())->toBeTrue();

    $user->delete();

    expect(Task::where('id', $task->id)->exists())->toBeFalse();
});
