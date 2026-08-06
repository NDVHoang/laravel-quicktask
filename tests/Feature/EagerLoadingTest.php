<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function getAdmin_EL()
{
    return User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin', 'is_active' => true]);
}

test('users index collection has tasks relation loaded', function () {
    $this->actingAs(getAdmin_EL());
    User::factory(3)->has(Task::factory()->count(2))->create();

    $response = $this->get(route('users.index'));
    $response->assertOk();

    $users = $response->viewData('users');
    expect($users->first()->relationLoaded('tasks'))->toBeTrue();
});

test('users index renders without lazy loading exception when preventLazyLoading is enabled', function () {
    // Enable prevent lazy loading
    Model::preventLazyLoading(true);

    $this->actingAs(getAdmin_EL());
    User::factory(3)->has(Task::factory()->count(2))->create();

    // If lazy loading happens in the view, this will throw an exception and return 500
    $response = $this->get(route('users.index'));
    $response->assertOk();

    // Restore default
    Model::preventLazyLoading(false);
});

test('increasing the number of users does not increase task queries', function () {
    $this->actingAs(getAdmin_EL());

    // Create 2 users with tasks
    User::factory(2)->has(Task::factory()->count(2))->create();

    DB::enableQueryLog();
    $this->get(route('users.index'));
    $queriesWith2Users = count(DB::getQueryLog());
    DB::disableQueryLog();

    // The queries with 10 users should be exactly the same as queries with 2 users
    // Because eager loading executes a fixed number of queries (e.g. 1 for users, 1 for tasks in IN clause)
    // To prove this without migrate:fresh (which breaks SQLite memory transactions):
    // Let's just create 8 more users and re-run.
    User::factory(8)->has(Task::factory()->count(2))->create();

    DB::flushQueryLog();
    DB::enableQueryLog();
    $this->get(route('users.index'));
    $queriesWith10Users = count(DB::getQueryLog());
    DB::disableQueryLog();

    // The number of queries should remain the same regardless of how many users exist
    // It should be 1 for auth, 1 for users, 1 for tasks = ~3 or similar.
    expect($queriesWith10Users)->toEqual($queriesWith2Users);
});
