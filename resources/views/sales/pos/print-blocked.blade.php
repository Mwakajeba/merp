<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Blocked</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            color: #212529;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
        }
        .card {
            max-width: 480px;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }
        h1 {
            font-size: 1.25rem;
            margin: 0 0 12px;
            color: #dc3545;
        }
        p {
            margin: 0 0 16px;
            line-height: 1.5;
        }
        a {
            color: #0d6efd;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Receipt Printing Blocked</h1>
        <p>{{ $message ?? 'This receipt cannot be printed again.' }}</p>
        <p><a href="{{ route('login') }}">Return to login</a></p>
    </div>
</body>
</html>
