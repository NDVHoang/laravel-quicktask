<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('password mutator hashes plaintext password on save', function () {
    $user = new User;
    $user->name = 'Test User';
    $user->email = 'test1@example.com';
    $user->password = 'secret-plaintext';
    $user->save();

    $storedPassword = $user->getRawOriginal('password');

    expect($storedPassword)->not->toBeNull()
        ->and($storedPassword)->not->toBe('secret-plaintext')
        ->and(Hash::check('secret-plaintext', $storedPassword))->toBeTrue();
});

test('password mutator creates new hash when updated with new plaintext', function () {
    $user = new User;
    $user->name = 'Test User';
    $user->email = 'test2@example.com';
    $user->password = 'secret-plaintext';
    $user->save();

    $user->password = 'new-secret-plaintext';
    $user->save();

    $storedPassword = $user->getRawOriginal('password');

    expect($storedPassword)->not->toBe('new-secret-plaintext')
        ->and(Hash::check('new-secret-plaintext', $storedPassword))->toBeTrue()
        ->and(Hash::check('secret-plaintext', $storedPassword))->toBeFalse();
});

test('password mutator does not rehash an already hashed password', function () {
    $validHash = Hash::make('secret-plaintext');

    $user = new User;
    $user->name = 'Test User';
    $user->email = 'test3@example.com';
    $user->password = $validHash;
    $user->save();

    $storedPassword = $user->getRawOriginal('password');

    expect($storedPassword)->toBe($validHash);
});

test('password does not appear in array or json representation', function () {
    $user = new User;
    $user->name = 'Test User';
    $user->email = 'test4@example.com';
    $user->password = 'secret-plaintext';
    $user->save();

    $array = $user->toArray();
    $json = $user->toJson();

    expect(array_key_exists('password', $array))->toBeFalse()
        ->and(str_contains($json, '"password"'))->toBeFalse();
});
