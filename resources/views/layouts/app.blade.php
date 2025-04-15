<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Session TODO List' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @stack('styles')
</head>

<body>
    <div class="container py-5">
        @yield('content')
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show alert if session was expired
            @if (session('status'))
                alert('{{ session('status') }}');
            @endif
        });

        // Improved tab closing detection
        window.addEventListener('beforeunload', function(e) {
            // Only trigger for actual tab closes (not refreshes/navigation)
            if (!window.performance || performance.navigation.type !== 1) {
                // Use fetch with keepalive instead of synchronous XHR
                fetch('/nuclear-reset', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({}),
                    keepalive: true // Ensures request completes
                });
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
