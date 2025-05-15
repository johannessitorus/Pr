<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> {{-- Menggunakan helper Laravel untuk lang --}}
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/png" href="{{ asset('foto/del.png') }}"> {{-- type="image/png" lebih tepat --}}
    <title>Login - SIPA {{-- Atau {{ config('app.name', 'SIPA') }} --}}</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    {{-- Tambahkan link untuk Bootstrap Icons jika Anda menggunakannya --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        /* Tambahan CSS untuk error message jika tidak ada di login.css */
        .field .error-message {
            color: red;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block; /* agar muncul di baris baru */
        }
        .alert.alert-danger ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            color: red;
        }
        .alert.alert-danger {
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid red;
            border-radius: 0.25rem;
            background-color: #f8d7da;
        }
        .logo-besar {
            width: 200px; /* Ganti ukuran sesuai keinginan */
            height: auto; /* Menjaga rasio aspek */
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="form-wrapper">
            <img src="{{ asset('foto/sipa.png') }}" alt="Logo IT Del" class="logo-besar">
            <h1>Institut Teknologi Del</h1>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                    @foreach ($errors->all() as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="field">
                    {{-- ASUMSI: Anda login menggunakan email. Jika username, ganti type="email" ke "text" dan name="email" ke "username" --}}
                    <input class="inp" type="email" value="{{ old('email') }}" name="email" id="email" required autocomplete="email" autofocus>
                    <label class="label" for="email">Alamat Email</label> {{-- Sesuaikan label --}}
                    <span class="bi bi-person"></span> {{-- Atau bi-envelope jika email --}}
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field">
                    <input class="inp" type="password" name="password" id="password" required autocomplete="current-password">
                    <label class="label" for="password">Password</label>
                    <span class="toggle-pass bi bi-eye"></span>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="action">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Ingat Saya</label> {{-- Sesuaikan teks --}}
                </div>

                <input type="submit" value="Login" id="login-btn">
            </form>
        </div>
        <div class="bg"></div>
   </div>

<script>
    const passwordInput = document.querySelector('#password'); // Lebih spesifik menargetkan input password
    const togglePasswordIcon = document.querySelector('.toggle-pass');

    if (togglePasswordIcon && passwordInput) { // Pastikan elemen ada
        togglePasswordIcon.addEventListener('click', () => {
            togglePasswordIcon.classList.toggle('bi-eye-slash');
            togglePasswordIcon.classList.toggle('bi-eye');
            passwordInput.type = (passwordInput.type === 'password') ? 'text' : 'password';
        });
    }
</script>
</body>
</html>
