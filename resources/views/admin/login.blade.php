<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin login · {{ config('site.brand') }}</title>
    <link rel="icon" href="{{ \App\Support\Assets::url('favicon.ico') }}" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) }}">
</head>
<body class="admin login-page">
    <main class="login-card">
        <div class="login-card__brand">
            <img src="{{ \App\Support\Assets::url('img/logo.png') }}" alt="{{ config('site.name') }}" width="96" height="101">
            <h1>Admin login</h1>
            <p>{{ config('site.name') }}</p>
        </div>

        @if ($errors->any())
            <div class="flash flash--error" role="alert"><x-icon name="alert" :size="20" /> <div>{{ $errors->first() }}</div></div>
        @endif

        <form method="post" action="{{ route('admin.login.store') }}">
            @csrf
            <div class="fld">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            </div>
            <div class="fld">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <div class="fld">
                <label class="check"><input type="checkbox" name="remember" value="1"> <span>Keep me signed in</span></label>
            </div>
            <button class="btn btn--primary btn--block" type="submit">Sign in</button>
        </form>
    </main>
</body>
</html>
