@csrf

<div class="space-y-6">
    <div>
        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Title') }}</label>
        <input type="text" name="title" id="title" value="{{ old('title', $task->title ?? '') }}" class="mt-1 block w-full rounded-md dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
        @error('title')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('User') }}</label>
        <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
            <option value="">{{ __('Select a user') }}</option>
            @foreach($users as $userOption)
                <option value="{{ $userOption->id }}" @selected(old('user_id', $task->user_id ?? $defaultUserId ?? '') == $userOption->id)>
                    {{ $userOption->name }}
                </option>
            @endforeach
        </select>
        @error('user_id')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Status') }}</label>
        <select name="status" id="status" class="mt-1 block w-full rounded-md dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
            <option value="pending" @selected(old('status', $task->status ?? '') == 'pending')>{{ __('pending') }}</option>
            <option value="in_progress" @selected(old('status', $task->status ?? '') == 'in_progress')>{{ __('in_progress') }}</option>
            <option value="completed" @selected(old('status', $task->status ?? '') == 'completed')>{{ __('completed') }}</option>
        </select>
        @error('status')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Due Date') }}</label>
        <input type="date" name="due_date" id="due_date" value="{{ old('due_date', (isset($task->due_date) && $task->due_date) ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-md dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        @error('due_date')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description') }}</label>
        <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $task->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex justify-end space-x-3">
    <x-ui.button href="{{ route('tasks.index') }}" variant="secondary">{{ __('Cancel') }}</x-ui.button>
    <x-ui.button type="submit" variant="primary">
        {{ isset($task) ? __('Update') : __('Save') }}
    </x-ui.button>
</div>
