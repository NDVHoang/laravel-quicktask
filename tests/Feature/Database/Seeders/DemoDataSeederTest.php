<?php

use App\Models\Task;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

test('demo data seeder creates 10 regular users', function () {
    $this->seed(DemoDataSeeder::class);

    $users = User::all();
    expect($users)->toHaveCount(10);

    // Check no admin
    $admins = User::where('role', 'admin')->get();
    expect($admins)->toHaveCount(0);
});

test('each user has exactly 3 tasks with valid foreign key', function () {
    $this->seed(DemoDataSeeder::class);

    $users = User::with('tasks')->get();

    foreach ($users as $user) {
        expect($user->tasks)->toHaveCount(3);
        foreach ($user->tasks as $task) {
            expect($task->user_id)->toBe($user->id);
        }
    }
});

test('demo data seeder refuses to run on production', function () {
    // Mock the environment
    App::detectEnvironment(fn () => 'production');

    $seeder = new DemoDataSeeder;
    $seeder->run();

    expect(User::count())->toBe(0)
        ->and(Task::count())->toBe(0);
});
