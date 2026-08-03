<?php

use App\Http\Middleware\CheckSuperAdmin;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('super_admin alias is mapped to CheckSuperAdmin middleware', function () {
    /** @var Illuminate\Foundation\Http\Kernel $kernel */
    $kernel = app(Kernel::class);
    $aliases = $kernel->getMiddlewareAliases();

    expect($aliases)->toHaveKey('super_admin', CheckSuperAdmin::class);
});

test('middleware rejects guests', function () {
    $request = Request::create('/users', 'GET');
    $middleware = new CheckSuperAdmin;

    $middleware->handle($request, function () {
        // Next should not be called
    });
})->throws(HttpException::class, 'Unauthorized action.');

test('middleware rejects normal users', function () {
    $user = User::factory()->make(['role' => 'user']);
    $request = Request::create('/users', 'GET');
    $request->setUserResolver(fn () => $user);
    $middleware = new CheckSuperAdmin;

    $middleware->handle($request, function () {
        // Next should not be called
    });
})->throws(HttpException::class, 'Unauthorized action.');

test('middleware allows super admin', function () {
    $admin = User::factory()->make(['role' => 'admin']);
    $request = Request::create('/users', 'GET');
    $request->setUserResolver(fn () => $admin);
    $middleware = new CheckSuperAdmin;

    $nextCalled = false;
    $response = $middleware->handle($request, function ($req) use (&$nextCalled) {
        $nextCalled = true;

        return response('OK');
    });

    expect($nextCalled)->toBeTrue()
        ->and($response->getContent())->toBe('OK');
});
