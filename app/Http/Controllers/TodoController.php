<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TodoController extends Controller
{
    public function index()
    {
        $todos = Session::get('todos', []);
        return view('todos.index', compact('todos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'task' => 'required|string|max:255',
        ]);

        $todos = Session::get('todos', []);

        // Add new task to the beginning of the array (latest first)
        array_unshift($todos, [
            'id' => uniqid(),
            'task' => $request->task,
            'created_at' => now()->toDateTimeString()
        ]);

        Session::put('todos', $todos);

        return response()->json([
            'success' => true,
            'todo' => $todos[0] // Return the newly added task
        ]);
    }

    public function destroy($id)
    {
        $todos = Session::get('todos', []);

        $todos = array_filter($todos, function($todo) use ($id) {
            return $todo['id'] !== $id;
        });

        Session::put('todos', array_values($todos));

        return response()->json(['success' => true]);
    }
}
