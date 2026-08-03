<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user tasks method returns HasMany relationship', function () {
    $user = new User;
    expect($user->tasks())->toBeInstanceOf(HasMany::class);
});

test('task user method returns BelongsTo relationship', function () {
    $task = new Task;
    expect($task->user())->toBeInstanceOf(BelongsTo::class);
});

test('task can be created via relationship and automatically receives user_id', function () {
    $user = User::factory()->create();

    $task = $user->tasks()->create([
        'title' => 'Relationship Task',
        'description' => 'Created via relationship',
    ]);

    expect($task->user_id)->toBe($user->id)
        ->and($task->title)->toBe('Relationship Task')
        ->and($task->id)->not->toBeNull();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'user_id' => $user->id,
    ]);
});

test('user tasks dynamic property returns collection of tasks', function () {
    $user = User::factory()->create();
    $task1 = $user->tasks()->create(['title' => 'Task 1']);
    $task2 = $user->tasks()->create(['title' => 'Task 2']);

    $tasks = $user->fresh()->tasks;

    expect($tasks)->toHaveCount(2)
        ->and($tasks->pluck('id')->toArray())->toEqualCanonicalizing([$task1->id, $task2->id]);
});

test('task user dynamic property returns the owner user', function () {
    $user = User::factory()->create();
    $task = $user->tasks()->create(['title' => 'Task 1']);

    $owner = $task->fresh()->user;

    expect($owner->id)->toBe($user->id)
        ->and($owner->name)->toBe($user->name);
});

test('user only sees tasks belonging to them through relationship', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $task1 = $user1->tasks()->create(['title' => 'User 1 Task']);
    $task2 = $user2->tasks()->create(['title' => 'User 2 Task']);

    $user1Tasks = $user1->fresh()->tasks;

    expect($user1Tasks)->toHaveCount(1)
        ->and($user1Tasks->first()->id)->toBe($task1->id)
        ->and($user1Tasks->contains('id', $task2->id))->toBeFalse();
});

test('creating task via relationship does not require user_id in fillable', function () {
    $user = User::factory()->create();

    $task = $user->tasks()->create([
        'title' => 'Safe Mass Assignment',
    ]);

    expect($task->user_id)->toBe($user->id);
});
