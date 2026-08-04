<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\withSession;

test('login page shares en translations when locale is en', function () {
    withSession(['locale' => 'en'])
        ->get('/login')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Login')
            ->where('locale', 'en')
            ->has('translations.auth_ui')
        );
});

test('login page shares vi translations when locale is vi', function () {
    withSession(['locale' => 'vi'])
        ->get('/login')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Login')
            ->where('locale', 'vi')
            ->has('translations.auth_ui')
        );
});

test('register page properly shares vi locale', function () {
    withSession(['locale' => 'vi'])
        ->get('/register')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Register')
            ->where('locale', 'vi')
        );
});

test('auth validation error uses correct locale', function () {
    withSession(['locale' => 'vi'])
        ->post('/login', [
            'email' => '',
            'password' => '',
        ])
        ->assertSessionHasErrors('email')
        ->assertSessionHasErrors('password');

    $errors = session('errors')->getBag('default');
    expect($errors->first('email'))->toBe('Trường địa chỉ email là bắt buộc.');
});

test('auth error for wrong password uses correct locale', function () {
    $user = User::factory()->create();

    withSession(['locale' => 'vi'])
        ->post('/login', [
            'email' => $user->email,
            'password' => 'wrong',
        ])
        ->assertSessionHasErrors('email');

    $errors = session('errors')->getBag('default');
    expect($errors->first('email'))->toBe('Thông tin đăng nhập không khớp với dữ liệu của chúng tôi.');
});
