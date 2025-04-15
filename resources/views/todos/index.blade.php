@extends('layouts.app')

@section('title', 'Session TODO List')

@push('styles')
    <style>
        .todo-item {
            transition: all 0.3s ease;
        }

        .todo-item:hover {
            background-color: #f8f9fa;
        }

        .session-info {
            font-size: 0.8rem;
            opacity: 0.7;
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Session TODO List</h4>
                    <small class="text-white-50">Your tasks will persist during this session only</small>
                </div>

                <div class="card-body">
                    <form id="todo-form" class="mb-4">
                        <div class="input-group">
                            <input type="text" id="task-input" class="form-control" placeholder="Add a new task..."
                                required>
                            <button type="submit" class="btn btn-primary">Add Task</button>
                        </div>
                    </form>

                    <div id="todo-list">
                        @foreach ($todos as $todo)
                            <div class="todo-item d-flex justify-content-between align-items-center p-3 border-bottom"
                                data-id="{{ $todo['id'] }}">
                                <span>{{ $todo['task'] }}</span>
                                <button class="btn btn-sm btn-danger delete-btn">×</button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card-footer text-muted">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="session-info">Session ID: {{ Session::getId() }}</small>
                        {{-- <small class="session-info">Started:
                            {{ now()->diffForHumans(\Carbon\Carbon::createFromTimestamp(Session::get('last_activity', time())), true) }}
                            ago
                        </small> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Add new task
            $('#todo-form').on('submit', function(e) {
                e.preventDefault();
                const taskInput = $('#task-input');
                const task = taskInput.val().trim();

                if (task) {
                    $.ajax({
                        url: "{{ route('todos.store') }}",
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            task: task
                        },
                        success: function(response) {
                            if (response.success) {
                                // Prepend new task to the list
                                $('#todo-list').prepend(`
                            <div class="todo-item d-flex justify-content-between align-items-center p-3 border-bottom" data-id="${response.todo.id}">
                                <span>${response.todo.task}</span>
                                <button class="btn btn-sm btn-danger delete-btn">×</button>
                            </div>
                        `);
                                taskInput.val('');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 401) {
                                alert('Your session has expired. Page will refresh.');
                                window.location.reload();
                            }
                        }
                    });
                }
            });

            // Delete task
            $(document).on('click', '.delete-btn', function() {
                const todoItem = $(this).closest('.todo-item');
                const id = todoItem.data('id');

                $.ajax({
                    url: `/todos/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        todoItem.remove();
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            alert('Your session has expired. Page will refresh.');
                            window.location.reload();
                        }
                    }
                });
            });

            // Session timeout management
            let sessionWarningTimer;
            const sessionLifetime = {{ config('session.lifetime') * 60 }}; // in seconds
            const warningTime = 60; // Show warning 1 minute before expiry

            function startSessionTimer() {
                clearTimeout(sessionWarningTimer);

                // Set timer to show warning 1 minute before session expires
                sessionWarningTimer = setTimeout(() => {
                    if (confirm(
                            'Your session will expire in 1 minute due to inactivity. Continue session?')) {
                        // Reset activity by making a request
                        $.get('/ping');
                        startSessionTimer(); // Restart the timer
                    }
                }, (sessionLifetime - warningTime) * 1000);
            }

            // Reset timer on user activity
            $(document).on('mousemove keydown click', function() {
                startSessionTimer();
            });

            // Start the timer initially
            startSessionTimer();

            // Check session status every minute
            setInterval(() => {
                $.get('/ping').fail(() => {
                    alert('Session expired. Page will refresh.');
                    window.location.reload();
                });
            }, 60000);
        });
    </script>
@endpush
