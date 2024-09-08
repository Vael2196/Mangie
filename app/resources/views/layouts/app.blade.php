<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management System</title>
    <!-- Include TailwindCSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Navbar or header can be placed here -->
        
        <main class="flex-grow">
            @yield('content')
        </main>

        <!-- Footer can be placed here -->
    </div>

    <!-- Add JS scripts if needed -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>