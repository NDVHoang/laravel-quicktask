@extends('layouts.management')

@section('title', __('Edit User') . ' - Quicktask')

@section('header', __('Edit User'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-8 max-w-2xl mx-auto">
    <div class="px-4 py-5 sm:p-6">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @method('PUT')
            @include('users._form', ['user' => $user])
        </form>
    </div>
</div>
@endsection
