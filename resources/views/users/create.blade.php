@extends('layouts.management')

@section('title', __('Create User') . ' - Quicktask')

@section('header', __('Create User'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-8 max-w-2xl mx-auto">
    <div class="px-4 py-5 sm:p-6">
        <form action="{{ route('users.store') }}" method="POST">
            @include('users._form')
        </form>
    </div>
</div>
@endsection
