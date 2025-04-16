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
                        <small class="session-info" id="session-timer">02:00</small>
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
            // Session timeout set to 2 minutes (120 seconds)
            const SESSION_LIFETIME = 120;
            let timeLeft = SESSION_LIFETIME;
            let warningShown = false;
            let sessionTimer;
            let activityTimer;

            // Update the visible timer
            function updateTimerDisplay() {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                $('#session-timer').text(
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
                );
            }

            // Start the countdown
            function startSessionTimer() {
                clearInterval(sessionTimer);
                timeLeft = SESSION_LIFETIME;
                updateTimerDisplay();
                warningShown = false;
                $('#session-warning').addClass('d-none');

                sessionTimer = setInterval(() => {
                    timeLeft--;
                    updateTimerDisplay();

                    // Show warning at 30 seconds
                    if (timeLeft <= 30 && !warningShown) {
                        $('#session-warning').removeClass('d-none');
                        warningShown = true;
                    }

                    // End session when time runs out
                    if (timeLeft <= 0) {
                        clearInterval(sessionTimer);
                        alert('Your session has expired due to inactivity.');
                        window.location.reload();
                    }
                }, 1000);
            }

            // Reset timer on any user activity
            function resetInactivityTimer() {
                clearTimeout(activityTimer);
                activityTimer = setTimeout(() => {
                    // Optional: You can add additional handling here if needed
                }, 1000);
                startSessionTimer();
            }

            // Set up event listeners for user activity
            $(document).on('mousemove keydown click scroll', resetInactivityTimer);

            // Initialize the timer
            startSessionTimer();

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
                                $('#todo-list').prepend(`
                                <div class="todo-item d-flex justify-content-between align-items-center p-3 border-bottom"
                                     data-id="${response.todo.id}">
                                    <span>${response.todo.task}</span>
                                    <button class="btn btn-sm btn-danger delete-btn">×</button>
                                </div>
                            `);
                                taskInput.val('');
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
                    }
                });
            });
            window.addEventListener('beforeunload', function(e) {
                if (!window.performance || performance.navigation.type !== 1) {
                    fetch('/nuclear-reset', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        },
                        body: JSON.stringify({}),
                        keepalive: true
                    });
                }
            });
        });
    </script>
@endpush
