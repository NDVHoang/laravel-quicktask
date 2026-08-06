@extends('layouts.management')

@section('title', __('User details') . ' - Quicktask')

@section('header', __('User details'))

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('users.index') }}" variant="secondary">
        &larr; {{ __('Back') }}
    </x-ui.button>
</div>

@if (session('success'))
    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

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
            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}');">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger">{{ __('Delete') }}</x-ui.button>
            </form>
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
