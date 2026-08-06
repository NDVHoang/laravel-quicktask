<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $tasks = DB::table('tasks')
            ->leftJoin('users', 'tasks.user_id', '=', 'users.id')
            ->select('tasks.*', 'users.name as user_name', 'users.email as user_email')
            ->orderBy('tasks.id', 'desc')
            ->paginate(10);

        return view('tasks.index', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $users = DB::table('users')->select('id', 'name', 'email')->orderBy('name')->get();

        return view('tasks.create', [
            'users' => $users,
            'defaultUserId' => $request->query('user_id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        DB::table('tasks')->insert($validated);

        return redirect()->route('tasks.index')->with('success', __('Task created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $task = DB::table('tasks')
            ->leftJoin('users', 'tasks.user_id', '=', 'users.id')
            ->select('tasks.*', 'users.name as user_name', 'users.email as user_email')
            ->where('tasks.id', $id)
            ->first();

        if (! $task) {
            abort(404);
        }

        return view('tasks.show', [
            'task' => $task,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $task = DB::table('tasks')->where('id', $id)->first();
        if (! $task) {
            abort(404);
        }

        $users = DB::table('users')->select('id', 'name', 'email')->orderBy('name')->get();

        return view('tasks.edit', [
            'task' => $task,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, string $id): RedirectResponse
    {
        $taskExists = DB::table('tasks')->where('id', $id)->exists();
        if (! $taskExists) {
            abort(404);
        }

        $validated = $request->validated();
        $validated['updated_at'] = now();

        DB::table('tasks')->where('id', $id)->update($validated);

        return redirect()->route('tasks.show', $id)->with('success', __('Task updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $taskExists = DB::table('tasks')->where('id', $id)->exists();
        if (! $taskExists) {
            abort(404);
        }

        DB::table('tasks')->where('id', $id)->delete();

        return redirect()->route('tasks.index')->with('success', __('Task deleted successfully.'));
    }
}
