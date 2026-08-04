<?php

use Illuminate\Support\Facades\App;
use Tests\TestCase;

uses(TestCase::class);

test('required keys exist in both en and vi', function () {
    $enUi = require lang_path('en/auth_ui.php');
    $viUi = require lang_path('vi/auth_ui.php');

    expect(array_keys($enUi))->toEqual(array_keys($viUi));
});

test('custom translation catalog has exact same keys for en and vi', function () {
    $enLoc = require lang_path('en/localization.php');
    $viLoc = require lang_path('vi/localization.php');

    expect(array_keys($enLoc))->toEqual(array_keys($viLoc));
});

test('parameterized translation correctly replaces parameter', function () {
    App::setLocale('en');
    $enTranslation = __('localization.locale_changed', ['language' => 'Vietnamese']);
    expect($enTranslation)->toBe('Language changed to Vietnamese.');

    App::setLocale('vi');
    $viTranslation = __('localization.locale_changed', ['language' => 'Tiếng Việt']);
    expect($viTranslation)->toBe('Đã chuyển ngôn ngữ sang Tiếng Việt.');
});

test('pluralization returns correct form for 0, 1, and more', function () {
    App::setLocale('en');
    expect(trans_choice('localization.comments', 0))->toBe('No comments');
    expect(trans_choice('localization.comments', 1))->toBe('One comment');
    expect(trans_choice('localization.comments', 5))->toBe('5 comments');

    App::setLocale('vi');
    expect(trans_choice('localization.comments', 0))->toBe('Không có bình luận');
    expect(trans_choice('localization.comments', 1))->toBe('Một bình luận');
    expect(trans_choice('localization.comments', 5))->toBe('5 bình luận');
});
