<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name', 'Laravel') }}</title>
    <!-- Tambahkan CSS Anda di sini -->
    {{-- <link rel="stylesheet" href="{{ asset('css/your-custom-styles.css') }}"> --}}
    <style> /* Contoh CSS inline sederhana */
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f3f4f6; margin:0; }
        .login-container { background-color: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        input[type="email"], input[type="password"], input[type="text"] { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 0.25rem; box-sizing: border-box; }
        .error-message { color: red; font-size: 0.875rem; margin-top: 0.25rem; }
        button { background-color: #4CAF50; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.25rem; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .alert-danger ul { list-style-type: none; padding: 0; color: red; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login Aplikasi</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Alamat Email</label> {{-- Atau Username --}}
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" style="display: inline-block; margin-bottom:0;">Ingat Saya</label>
            </div>

            <div class="form-group">
                <button type="submit">Login</button>
            </div>
        </form>
        {{--
        <p>Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
        <p><a href="{{ route('password.request') }}">Lupa Password?</a></p>
        --}}
    </div>
</body>
</html>
