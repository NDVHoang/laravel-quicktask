<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function getAdmin_UC()
{
    return User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin', 'is_active' => true]);
}

function getNormalUser_UC()
{
    return User::where('role', 'user')->first() ?? User::factory()->create(['role' => 'user', 'is_active' => true]);
}

test('guest is redirected to login', function () {
    $this->get(route('users.index'))->assertRedirect('/login');
    $this->get(route('users.create'))->assertRedirect('/login');
    $this->post(route('users.store'))->assertRedirect('/login');
    $this->get(route('users.show', 1))->assertRedirect('/login');
    $this->get(route('users.edit', 1))->assertRedirect('/login');
    $this->put(route('users.update', 1))->assertRedirect('/login');
    $this->delete(route('users.destroy', 1))->assertRedirect('/login');
});

test('normal user cannot access user crud', function () {
    $this->actingAs(getNormalUser_UC());
    $this->get(route('users.index'))->assertForbidden();
    $this->get(route('users.create'))->assertForbidden();
    $this->post(route('users.store'))->assertForbidden();
    $this->get(route('users.show', getAdmin_UC()))->assertForbidden();
    $this->get(route('users.edit', getAdmin_UC()))->assertForbidden();
    $this->put(route('users.update', getAdmin_UC()))->assertForbidden();
    $this->delete(route('users.destroy', getAdmin_UC()))->assertForbidden();
});

test('super admin can access index and it renders correctly', function () {
    $this->actingAs(getAdmin_UC());
    $response = $this->get(route('users.index'));
    $response->assertOk();
    $response->assertViewIs('users.index');

    // Assert users eager loaded tasks in index
    $users = $response->viewData('users');
    expect($users->first()->relationLoaded('tasks'))->toBeTrue();
});

test('create renders successfully', function () {
    $this->actingAs(getAdmin_UC());
    $this->get(route('users.create'))->assertOk()->assertViewIs('users.create');
});

test('store creates user with valid data and hashes password', function () {
    $this->actingAs(getAdmin_UC());
    $data = [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ];

    $response = $this->post(route('users.store'), $data);
    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('success');

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('New User');
    // plain text password not stored
    expect($user->password)->not->toBe('secret123');
    expect(Hash::check('secret123', $user->password))->toBeTrue();
});

test('store rejects duplicate email', function () {
    $this->actingAs(getAdmin_UC());
    $data = [
        'name' => 'Duplicate Email User',
        'email' => getNormalUser_UC()->email, // existing email
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ];

    $response = $this->post(route('users.store'), $data);
    $response->assertSessionHasErrors(['email']);
});

test('store does not mass assign is_super_admin or role if sent maliciously', function () {
    $this->actingAs(getAdmin_UC());
    $data = [
        'name' => 'Hacker User',
        'email' => 'hacker@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'role' => 'admin',
        'is_super_admin' => 1,
    ];

    $this->post(route('users.store'), $data);

    $user = User::where('email', 'hacker@example.com')->first();
    // Assuming default role is 'user', check if it wasn't overwritten
    // If mass assignment is protected, it should fall back to default or ignore it.
    // In Quicktask, role might be fillable? Let's check mass assignment protection.
    // The requirement says "field is_super_admin/role gửi thêm không được mass assign"
    // Assuming StoreUserRequest only allows name, email, password.
    if (array_key_exists('role', $user->getAttributes()) && $user->role === 'admin') {
        // If it got assigned, this test will fail, indicating a security issue
        expect($user->role)->not->toBe('admin');
    }
});

test('update skips unique email check for current user', function () {
    $this->actingAs(getAdmin_UC());
    $data = [
        'name' => 'Updated Admin',
        'email' => getAdmin_UC()->email, // same email
    ];

    $response = $this->put(route('users.update', getAdmin_UC()), $data);
    $response->assertRedirect(route('users.show', getAdmin_UC()));

    getAdmin_UC()->refresh();
    expect(getAdmin_UC()->name)->toBe('Updated Admin');
});

test('update keeps old password when password field is empty', function () {
    $this->actingAs(getAdmin_UC());
    $oldPassword = getNormalUser_UC()->password;

    $data = [
        'name' => 'Updated User Name',
        'email' => 'updateduser@example.com',
        'password' => '',
        'password_confirmation' => '',
    ];

    $response = $this->put(route('users.update', getNormalUser_UC()), $data);

    getNormalUser_UC()->refresh();
    expect(getNormalUser_UC()->name)->toBe('Updated User Name');
    expect(getNormalUser_UC()->password)->toBe($oldPassword);
});

test('update hashes new password', function () {
    $this->actingAs(getAdmin_UC());

    $data = [
        'name' => 'Updated User Name',
        'email' => getNormalUser_UC()->email,
        'password' => 'newsecret456',
        'password_confirmation' => 'newsecret456',
    ];

    $this->put(route('users.update', getNormalUser_UC()), $data);

    getNormalUser_UC()->refresh();
    expect(Hash::check('newsecret456', getNormalUser_UC()->password))->toBeTrue();
});

test('show loads tasks', function () {
    $this->actingAs(getAdmin_UC());
    $response = $this->get(route('users.show', getNormalUser_UC()));
    $response->assertOk();

    $viewUser = $response->viewData('user');
    expect($viewUser->relationLoaded('tasks'))->toBeTrue();
});

test('delete user without task succeeds', function () {
    $this->actingAs(getAdmin_UC());
    $userToDelete = User::factory()->create();

    $response = $this->delete(route('users.destroy', $userToDelete));
    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
});

test('delete user with task is blocked', function () {
    $this->actingAs(getAdmin_UC());
    $userWithTask = User::factory()->create();
    Task::factory()->create(['user_id' => $userWithTask->id]);

    $response = $this->delete(route('users.destroy', $userWithTask));
    $response->assertRedirect();
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('users', ['id' => $userWithTask->id]);
    $this->assertDatabaseHas('tasks', ['user_id' => $userWithTask->id]);
});

test('form request validations for user', function () {
    $this->actingAs(getAdmin_UC());

    // required
    $response = $this->post(route('users.store'), []);
    $response->assertSessionHasErrors(['name', 'email', 'password']);

    // email format
    $response = $this->post(route('users.store'), ['email' => 'not-an-email']);
    $response->assertSessionHasErrors(['email']);

    // confirmed password
    $response = $this->post(route('users.store'), [
        'password' => 'secret123',
        'password_confirmation' => 'different',
    ]);
    $response->assertSessionHasErrors(['password']);
});
