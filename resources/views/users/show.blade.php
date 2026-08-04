@extends('layouts.management')

@section('title', __('User details') . ' - Quicktask')

@section('header', __('User details'))

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('users.index') }}" variant="secondary">
        &larr; {{ __('Back') }}
    </x-ui.button>
</div>

<div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-8">
    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
        <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                {{ $user->name }}
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                {{ $user->email }}
            </p>
        </div>
        <div class="space-x-2">
            <x-ui.button href="{{ route('users.edit', $user) }}" variant="primary">{{ __('Edit') }}</x-ui.button>
            <x-ui.button disabled variant="danger">{{ __('Delete') }}</x-ui.button>
        </div>
    </div>
    <div class="border-t border-gray-200 dark:border-gray-700">
        <dl>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Role') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                    {{ $user->role === 'admin' ? __('Admin') : __('User') }}
                </dd>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                    {{ $user->is_active ? __('Active') : __('Inactive') }}
                </dd>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created At') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                    {{ $user->created_at ? $user->created_at->format('Y-m-d H:i') : '-' }}
                </dd>
            </div>
        </dl>
    </div>
</div>

@include('tasks._table', ['tasks' => $tasks])
@endsection
