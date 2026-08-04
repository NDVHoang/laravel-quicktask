@extends('layouts.management')

@section('title', __('Users') . ' - Quicktask')

@section('header', __('Users'))

@section('content')
<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600 dark:text-gray-400">{{ __('Manage system users.') }}</p>
    <x-ui.button href="{{ route('users.create') }}" variant="primary">
        {{ __('Create') }}
    </x-ui.button>
</div>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Name') }}</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Email') }}</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Role') }}</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Status') }}</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $user->role === 'admin' ? __('Admin') : __('User') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $user->is_active ? __('Active') : __('Inactive') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <x-ui.button href="{{ route('users.show', $user) }}" variant="secondary" class="px-2 py-1 text-xs">{{ __('View') }}</x-ui.button>
                            <x-ui.button href="{{ route('users.edit', $user) }}" variant="primary" class="px-2 py-1 text-xs">{{ __('Edit') }}</x-ui.button>
                            <x-ui.button disabled variant="danger" class="px-2 py-1 text-xs">{{ __('Delete') }}</x-ui.button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4">
                            <x-ui.empty-state message="{{ __('No users found') }}" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
