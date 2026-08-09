<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Employee Rostering System</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    </head>

    <body>
        <div class="background welcome-page">
            <div class="hero-content">
                <div class="welcome-text">Welcome to</div>

                <h1 class="main-title">
                    Employee<br>
                    Rostering System
                </h1>

                <p class="subtitle">Manage your employees and their schedules easily and efficiently !</p>

                <a href="{{ url('userlogin') }}" class="btn btn-primary btn-start">
                    Let's Get Started
                    <span class="arrow">→</span>
                </a>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>