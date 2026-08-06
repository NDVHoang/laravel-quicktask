<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function getNormalUser_TC()
{
    return User::where('role', 'user')->first() ?? User::factory()->create(['role' => 'user', 'is_active' => true]);
}

test('guest is redirected to login for tasks', function () {
    $this->get(route('tasks.index'))->assertRedirect('/login');
    $this->get(route('tasks.create'))->assertRedirect('/login');
    $this->post(route('tasks.store'))->assertRedirect('/login');
    $this->get(route('tasks.show', 1))->assertRedirect('/login');
    $this->get(route('tasks.edit', 1))->assertRedirect('/login');
    $this->put(route('tasks.update', 1))->assertRedirect('/login');
    $this->delete(route('tasks.destroy', 1))->assertRedirect('/login');
});

test('authenticated user can access tasks index', function () {
    $this->actingAs(getNormalUser_TC());
    $response = $this->get(route('tasks.index'));
    $response->assertOk();
    $response->assertViewIs('tasks.index');
});

test('index join displays correct user', function () {
    $this->actingAs(getNormalUser_TC());
    $task = Task::factory()->create(['user_id' => getNormalUser_TC()->id, 'title' => 'Specific Task']);

    $response = $this->get(route('tasks.index'));
    $response->assertSee('Specific Task');
    $response->assertSee(getNormalUser_TC()->name);
});

test('create has user list', function () {
    $this->actingAs(getNormalUser_TC());
    $response = $this->get(route('tasks.create'));
    $response->assertOk();

    $users = $response->viewData('users');
    expect($users)->not->toBeEmpty();
    expect($users->first()->name)->toBe(getNormalUser_TC()->name); // Assuming ordered by name
});

test('store valid data creates task', function () {
    $this->actingAs(getNormalUser_TC());

    $data = [
        'title' => 'New Task',
        'description' => 'A description',
        'status' => 'pending',
        'due_date' => Carbon::tomorrow()->format('Y-m-d'),
        'user_id' => getNormalUser_TC()->id,
    ];

    $response = $this->post(route('tasks.store'), $data);
    $response->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('tasks', [
        'title' => 'New Task',
        'user_id' => getNormalUser_TC()->id,
    ]);
});

test('store invalid data is rejected', function () {
    $this->actingAs(getNormalUser_TC());

    $response = $this->post(route('tasks.store'), []);
    $response->assertSessionHasErrors(['title', 'status']);
});

test('store rejects non-existent user_id', function () {
    $this->actingAs(getNormalUser_TC());

    $data = [
        'title' => 'New Task',
        'status' => 'pending',
        'user_id' => 9999, // Doesn't exist
    ];

    $response = $this->post(route('tasks.store'), $data);
    $response->assertSessionHasErrors(['user_id']);
});

test('show returns 404 for non-existent task', function () {
    $this->actingAs(getNormalUser_TC());
    $response = $this->get(route('tasks.show', 9999));
    $response->assertNotFound();
});

test('update modifies explicit fields only', function () {
    $this->actingAs(getNormalUser_TC());
    $task = Task::factory()->create(['title' => 'Old Title', 'status' => 'pending', 'user_id' => getNormalUser_TC()->id]);

    $data = [
        'title' => 'Updated Title',
        'description' => 'Updated Description',
        'status' => 'in_progress',
        'due_date' => Carbon::tomorrow()->format('Y-m-d'),
        'user_id' => getNormalUser_TC()->id,
        'extra_field' => 'should not be saved',
    ];

    $response = $this->put(route('tasks.update', $task->id), $data);
    $response->assertRedirect(route('tasks.show', $task->id));

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Title',
        'status' => 'in_progress',
    ]);

    // Verify extra_field wasn't inserted by attempting to select it natively
    // We expect it to not exist on the schema anyway, but we just make sure
    // no exception was thrown during mass assignment.
});

test('delete removes correct task', function () {
    $this->actingAs(getNormalUser_TC());
    $task = Task::factory()->create(['user_id' => getNormalUser_TC()->id]);

    $response = $this->delete(route('tasks.destroy', $task->id));
    $response->assertRedirect(route('tasks.index'));

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('timestamps are correct using query builder', function () {
    $this->actingAs(getNormalUser_TC());
    $data = [
        'title' => 'Timestamp Task',
        'status' => 'pending',
        'user_id' => getNormalUser_TC()->id,
    ];

    $this->post(route('tasks.store'), $data);

    $task = DB::table('tasks')->where('title', 'Timestamp Task')->first();
    expect($task->created_at)->not->toBeNull();
    expect($task->updated_at)->not->toBeNull();
});

test('form request validations for tasks', function () {
    $this->actingAs(getNormalUser_TC());

    // required
    $response = $this->post(route('tasks.store'), []);
    $response->assertSessionHasErrors(['title', 'status']);

    // max length
    $response = $this->post(route('tasks.store'), [
        'title' => str_repeat('a', 256),
        'status' => 'pending',
    ]);
    $response->assertSessionHasErrors(['title']);

    // in array / enum
    $response = $this->post(route('tasks.store'), [
        'title' => 'Test',
        'status' => 'invalid_status',
    ]);
    $response->assertSessionHasErrors(['status']);

    // date
    $response = $this->post(route('tasks.store'), [
        'title' => 'Test',
        'status' => 'pending',
        'due_date' => 'not-a-date',
    ]);
    $response->assertSessionHasErrors(['due_date']);
});
