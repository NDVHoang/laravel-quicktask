<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;

test('guest cannot access user management', function () {
    $this->get(route('users.index'))->assertRedirect('/login');
    $this->get(route('users.show', 1))->assertRedirect('/login');
});

test('normal user cannot access user management', function () {
    $user = User::factory()->create(['role' => 'user']);
    $this->actingAs($user)->get(route('users.index'))->assertForbidden();
    $this->actingAs($user)->get(route('users.show', 1))->assertForbidden();
});

test('super admin can access user management', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get(route('users.index'))->assertOk();
    $this->actingAs($admin)->get(route('users.show', $admin))->assertOk();
});

test('users index renders empty state', function () {
    $view = $this->view('users.index', ['users' => collect([])]);
    $view->assertSee('No users found');
});

test('users index renders users list safely', function () {
    $user = new User([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret',
    ]);
    $user->id = 99;
    $user->role = 'admin';
    $user->is_active = true;

    $view = $this->view('users.index', ['users' => collect([$user])]);
    $view->assertSee('John Doe');
    $view->assertSee('john@example.com');
    $view->assertSee('Admin');
    $view->assertSee('Active');
    $view->assertSee(route('users.show', $user));
});

test('users show renders empty tasks state', function () {
    $user = new User([
        'name' => 'John Doe',
        'email' => 'test@test.com',
        'password' => 'secret',
    ]);
    $user->id = 1;
    $view = $this->view('users.show', ['user' => $user, 'tasks' => collect([])]);

    $view->assertSee('John Doe');
    $view->assertSee('No tasks found');
    $view->assertSee(route('users.index'));
});

test('users show renders related tasks list', function () {
    $user = new User([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'secret',
    ]);
    $user->id = 1;

    $task = new Task([
        'title' => 'Finish chapter 11',
        'description' => 'Write tests for chapter 11',
        'status' => 'pending',
    ]);
    $task->id = 1;
    $task->created_at = Carbon::now();

    $view = $this->view('users.show', ['user' => $user, 'tasks' => collect([$task])]);
    $view->assertSee('Jane Doe');
    $view->assertSee('Finish chapter 11');
    $view->assertSee('pending');
    $view->assertSee(route('tasks.show', $task));
});

test('blade templates escape dangerous user input', function () {
    $maliciousName = '<script>alert("XSS User")</script>';
    $user = new User(['name' => $maliciousName, 'email' => 'test@test.com', 'password' => 'secret']);
    $user->id = 1;

    $view = $this->view('users.show', ['user' => $user, 'tasks' => collect([])]);

    $view->assertDontSee($maliciousName, false);
    $view->assertSee('&lt;script&gt;alert(&quot;XSS User&quot;)&lt;/script&gt;', false);
});

test('blade templates escape dangerous task input', function () {
    $user = new User(['name' => 'Safe User', 'email' => 'test@test.com', 'password' => 'secret']);
    $user->id = 1;
    $maliciousTitle = '<script>alert("XSS Task")</script>';
    $task = new Task(['title' => $maliciousTitle, 'description' => 'desc', 'status' => 'pending']);
    $task->id = 1;

    $view = $this->view('users.show', ['user' => $user, 'tasks' => collect([$task])]);

    $view->assertDontSee($maliciousTitle, false);
    $view->assertSee('&lt;script&gt;alert(&quot;XSS Task&quot;)&lt;/script&gt;', false);
});

test('delete button uses DELETE form', function () {
    $user = new User(['name' => 'Test', 'email' => 'test@test.com', 'password' => 'secret']);
    $user->id = 1;
    $view = $this->view('users.index', ['users' => collect([$user])]);

    $view->assertSee('method="POST"', false);
    $view->assertSee('value="DELETE"', false);
    $view->assertDontSee('<form method="GET"', false);
});

test('layout sets language correctly', function () {
    app()->setLocale('vi');
    $view = $this->view('layouts.management');
    $view->assertSee('<html lang="vi">', false);

    app()->setLocale('en');
    $view = $this->view('layouts.management');
    $view->assertSee('<html lang="en">', false);
});
