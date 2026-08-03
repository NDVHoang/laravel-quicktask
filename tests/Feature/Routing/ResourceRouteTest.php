<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

test('UserController has exactly 7 resource actions', function () {
    $methods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

    foreach ($methods as $method) {
        expect(method_exists(UserController::class, $method))->toBeTrue();
    }
});

test('TaskController has exactly 7 resource actions', function () {
    $methods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

    foreach ($methods as $method) {
        expect(method_exists(TaskController::class, $method))->toBeTrue();
    }
});

test('controller methods use correct route model binding signatures', function () {
    $userModelActions = ['show', 'edit', 'update', 'destroy'];
    foreach ($userModelActions as $action) {
        $reflection = new ReflectionMethod(UserController::class, $action);
        $parameters = $reflection->getParameters();
        $hasUserModel = false;
        foreach ($parameters as $param) {
            if ($param->getName() === 'user' && $param->getType()?->getName() === User::class) {
                $hasUserModel = true;
                break;
            }
        }
        expect($hasUserModel)->toBeTrue("UserController@{$action} should have a 'User \$user' parameter.");
    }

    $taskModelActions = ['show', 'edit', 'update', 'destroy'];
    foreach ($taskModelActions as $action) {
        $reflection = new ReflectionMethod(TaskController::class, $action);
        $parameters = $reflection->getParameters();
        $hasTaskModel = false;
        foreach ($parameters as $param) {
            if ($param->getName() === 'task' && $param->getType()?->getName() === Task::class) {
                $hasTaskModel = true;
                break;
            }
        }
        expect($hasTaskModel)->toBeTrue("TaskController@{$action} should have a 'Task \$task' parameter.");
    }
});

test('users routes are registered correctly with correct URIs and methods', function () {
    $expected = [
        'users.index' => ['methods' => ['GET', 'HEAD'], 'uri' => 'users', 'action' => 'index'],
        'users.create' => ['methods' => ['GET', 'HEAD'], 'uri' => 'users/create', 'action' => 'create'],
        'users.store' => ['methods' => ['POST'],        'uri' => 'users', 'action' => 'store'],
        'users.show' => ['methods' => ['GET', 'HEAD'], 'uri' => 'users/{user}', 'action' => 'show'],
        'users.edit' => ['methods' => ['GET', 'HEAD'], 'uri' => 'users/{user}/edit', 'action' => 'edit'],
        'users.update' => ['methods' => ['PUT', 'PATCH'], 'uri' => 'users/{user}', 'action' => 'update'],
        'users.destroy' => ['methods' => ['DELETE'],      'uri' => 'users/{user}', 'action' => 'destroy'],
    ];

    foreach ($expected as $name => $config) {
        expect(Route::has($name))->toBeTrue("Route name {$name} should exist.");

        $route = Route::getRoutes()->getByName($name);
        expect(array_intersect($config['methods'], $route->methods()))->not->toBeEmpty();
        expect($route->uri())->toBe($config['uri']);
        expect($route->getActionName())->toContain(UserController::class.'@'.$config['action']);
    }
});

test('tasks routes are registered correctly with correct URIs and methods', function () {
    $expected = [
        'tasks.index' => ['methods' => ['GET', 'HEAD'], 'uri' => 'tasks', 'action' => 'index'],
        'tasks.create' => ['methods' => ['GET', 'HEAD'], 'uri' => 'tasks/create', 'action' => 'create'],
        'tasks.store' => ['methods' => ['POST'],        'uri' => 'tasks', 'action' => 'store'],
        'tasks.show' => ['methods' => ['GET', 'HEAD'], 'uri' => 'tasks/{task}', 'action' => 'show'],
        'tasks.edit' => ['methods' => ['GET', 'HEAD'], 'uri' => 'tasks/{task}/edit', 'action' => 'edit'],
        'tasks.update' => ['methods' => ['PUT', 'PATCH'], 'uri' => 'tasks/{task}', 'action' => 'update'],
        'tasks.destroy' => ['methods' => ['DELETE'],      'uri' => 'tasks/{task}', 'action' => 'destroy'],
    ];

    foreach ($expected as $name => $config) {
        expect(Route::has($name))->toBeTrue("Route name {$name} should exist.");

        $route = Route::getRoutes()->getByName($name);
        expect(array_intersect($config['methods'], $route->methods()))->not->toBeEmpty();
        expect($route->uri())->toBe($config['uri']);
        expect($route->getActionName())->toContain(TaskController::class.'@'.$config['action']);
    }
});

test('tasks update route accepts both PUT and PATCH', function () {
    $route = Route::getRoutes()->getByName('tasks.update');

    expect($route->methods())->toContain('PUT');
    expect($route->methods())->toContain('PATCH');
});

test('route precedence: GET /tasks/create maps to TaskController@create not show', function () {
    $request = Request::create('/tasks/create', 'GET');
    $route = Route::getRoutes()->match($request);

    expect($route->getActionName())->toContain(TaskController::class.'@create');
});

test('users routes have super_admin middleware', function () {
    $expected = [
        'users.index',
        'users.create',
        'users.store',
        'users.show',
        'users.edit',
        'users.update',
        'users.destroy',
    ];

    foreach ($expected as $name) {
        $route = Route::getRoutes()->getByName($name);
        expect($route->gatherMiddleware())->toContain('super_admin');
    }
});

test('tasks routes do not have super_admin middleware', function () {
    $expected = [
        'tasks.index',
        'tasks.create',
        'tasks.store',
        'tasks.show',
        'tasks.edit',
        'tasks.update',
        'tasks.destroy',
    ];

    foreach ($expected as $name) {
        $route = Route::getRoutes()->getByName($name);
        expect($route->gatherMiddleware())->not->toContain('super_admin');
    }
});
