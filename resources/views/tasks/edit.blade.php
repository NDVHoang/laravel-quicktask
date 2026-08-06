@extends('layouts.management')

@section('title', __('Edit Task') . ' - Quicktask')

@section('header', __('Edit Task'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-8 max-w-2xl mx-auto">
    <div class="px-4 py-5 sm:p-6">
        <form action="{{ route('tasks.update', $task->id) }}" method="POST">
            @method('PUT')
            @include('tasks._form', ['task' => $task])
        </form>
    </div>
</div>
@endsection
