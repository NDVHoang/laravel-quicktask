@extends('layouts.management')

@section('title', __('Create Task') . ' - Quicktask')

@section('header', __('Create Task'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-8 max-w-2xl mx-auto">
    <div class="px-4 py-5 sm:p-6">
        <form action="{{ route('tasks.store') }}" method="POST">
            @include('tasks._form')
        </form>
    </div>
</div>
@endsection
