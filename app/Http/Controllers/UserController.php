<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::query()
            ->with('tasks')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('users.index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Password hashing is handled automatically by the model's mutator
        User::create($validated);

        return redirect()->route('users.index')->with('success', __('User created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $user->load('tasks');

        return view('users.show', [
            'user' => $user,
            'tasks' => $user->tasks,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.show', $user)->with('success', __('User updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->tasks()->exists()) {
            return redirect()->back()->with('error', __('Cannot delete user with tasks. Manual cascade will be implemented in Chapter 13.'));
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', __('User deleted successfully.'));
    }
}
