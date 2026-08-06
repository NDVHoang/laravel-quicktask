@csrf

<div class="space-y-6">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Name') }}</label>
        <input type="text" name="name" id="name" value="{{ old('name', $user->name ?? '') }}" autocomplete="name" class="mt-1 block w-full rounded-md dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
        @error('name')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Email') }}</label>
        <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" autocomplete="email" class="mt-1 block w-full rounded-md dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
        @error('email')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('Password') }} 
            @if(isset($user))
                <span class="text-gray-500 text-xs font-normal">({{ __('Leave blank to keep current password') }})</span>
            @endif
        </label>
        <input type="password" name="password" id="password" autocomplete="new-password" class="mt-1 block w-full rounded-md dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" {{ isset($user) ? '' : 'required' }}>
        @error('password')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Confirm Password') }}</label>
        <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" class="mt-1 block w-full rounded-md dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" {{ isset($user) ? '' : 'required' }}>
    </div>
</div>

<div class="mt-6 flex justify-end space-x-3">
    <x-ui.button href="{{ route('users.index') }}" variant="secondary">{{ __('Cancel') }}</x-ui.button>
    <x-ui.button type="submit" variant="primary">
        {{ isset($user) ? __('Update') : __('Save') }}
    </x-ui.button>
</div>
