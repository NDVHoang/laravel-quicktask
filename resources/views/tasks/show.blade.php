@extends('layouts.management')

@section('title', __('View task') . ' - Quicktask')

@section('header', __('View task'))

@section('content')
<div class="mb-6 flex justify-between items-center">
    <x-ui.button href="{{ route('tasks.index') }}" variant="secondary">
        &larr; {{ __('Back') }}
    </x-ui.button>
    <div class="space-x-2">
        <x-ui.button href="{{ route('tasks.edit', $task->id) }}" variant="primary">{{ __('Edit task') }}</x-ui.button>
        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this task?') }}');">
            @csrf
            @method('DELETE')
            <x-ui.button type="submit" variant="danger">{{ __('Delete task') }}</x-ui.button>
        </form>
    </div>
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
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
            {{ $task->title }}
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
            {{ __('Task details and information') }}
        </p>
    </div>
    <div class="border-t border-gray-200 dark:border-gray-700">
        <dl>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Owner') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                    {{ $task->user_name ?? '-' }} ({{ $task->user_email ?? '-' }})
                </dd>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                    {{ __($task->status) }}
                </dd>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Due Date') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                    {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '-' }}
                </dd>
            </div>
            <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created At') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                    {{ $task->created_at ? \Carbon\Carbon::parse($task->created_at)->format('Y-m-d H:i') : '-' }}
                </dd>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Description') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2 whitespace-pre-wrap">{{ $task->description ?: '-' }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
