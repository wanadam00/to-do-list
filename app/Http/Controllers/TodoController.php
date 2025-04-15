<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TodoController extends Controller
{
    public function index()
    {
        // Initialize session if this is a new one
        if (!Session::has('initialized')) {
            $this->initNewSession();
        }

        $todos = Session::get('todos', []);
        return view('todos.index', compact('todos'));
    }

    public function store(Request $request)
    {
        $request->validate(['task' => 'required|string|max:255']);

        $todos = Session::get('todos', []);
        array_unshift($todos, [
            'id' => uniqid(),
            'task' => $request->task,
            'created_at' => now()
        ]);

        Session::put('todos', $todos);
        Session::put('last_activity', time()); // Update activity timestamp

        return response()->json(['success' => true, 'todo' => $todos[0]]);
    }

    public function destroy($id)
    {
        $todos = array_filter(Session::get('todos', []), function ($todo) use ($id) {
            return $todo['id'] !== $id;
        });

        Session::put('todos', array_values($todos));
        Session::put('last_activity', time());

        return response()->json(['success' => true]);
    }

    public function nuclearReset()
    {
        // Completely destroy and regenerate session
        Session::flush();
        Session::regenerate(true);

        return response()->json(['success' => true]);
    }

    protected function initNewSession()
    {
        Session::flush();
        Session::regenerate(true); // Make sure a new session starts fresh
        Session::put('initialized', true);
        Session::put('todos', []);
        Session::put('last_activity', time());
    }
}
