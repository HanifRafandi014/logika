<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Arvegatu</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body, html {
        height: 100%;
        font-family: 'Poppins', sans-serif;
    }

    /* Container utama */
    .container {
        display: flex;
        min-height: 100vh;
    }

    /* Kiri */
    .left-side {
        flex: 1;
        background-color: #E0A800; /* Warna kuning */
        color: black;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 40px;
    }

    .left-side h1, h2 {
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 20px;
        font-family: 'Georgia', serif;
    }

    .left-side .mascot {
        width: 100%;
        max-width: 350px;
    }

    /* Kanan */
    .right-side {
        flex: 1;
        background-color: #f9f9f9;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding-top: 20px;
        padding-left: 120px;
        position: relative;
    }

    /* Kotak Form Login */
    .login-box {
        background: white;
        padding: 40px 30px;
        border-radius: 15px;
        box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 500px;
    }

    /* Title */
    .login-title {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 25px;
        text-align: center;
        color: #333;
    }

    /* Form Input */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #555;
        font-weight: bold;
    }

    .form-input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #ccc;
        border-radius: 10px;
        outline: none;
        font-size: 14px;
        transition: 0.3s;
    }

    .form-input:focus {
        border-color: #6b73ff;
        box-shadow: 0 0 8px rgba(107, 115, 255, 0.3);
    }

    /* Remember me */
    .remember-me {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .remember-me input {
        margin-right: 8px;
    }

    /* Tombol Login */
    .btn-login {
        width: 100%;
        background: #000;
        color: white;
        padding: 12px;
        font-size: 16px;
        font-weight: bold;
        border: none;
        border-radius: 50px;
        transition: 0.3s;
        cursor: pointer;
    }

    .btn-login:hover {
        background: #838788;
    }

    /* Link bagian bawah */
    .link-container {
        text-align: center;
        margin-top: 20px;
    }

    .link-container a {
        display: block;
        color: #6b73ff;
        text-decoration: none;
        font-size: 14px;
        margin-top: 5px;
    }

    .link-container a:hover {
        text-decoration: underline;
    }

    label {
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
        font-size: 14px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 15px;
        width: 100%;
        text-align: center;
    }

    .text-center {
        text-align: center;
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-side">
            <h1>Pramuka Arvegatu</h1>
            <img src="{{ asset('img/CENDRA 1.png') }}" alt="Maskot Pramuka" class="mascot">
        </div>
    
        <div class="right-side">
            <div class="login-box">
                <h2 class="login-title">Login Arvegatu</h2>
    
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
    
                <form method="POST" action="{{ route('login.attempt') }}" class="user" id="loginForm">
                    @csrf
                    
                    <!-- Username Input -->
                    <div class="form-group">
                        <label for="exampleInputUsername" class="form-label">Username</label>
                        <input type="text" name="username" class="form-input"
                            id="exampleInputUsername" placeholder="Masukkan Username"
                            value="{{ old('username') }}" required autofocus>
                        @error('username')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
    
                    <!-- Password Input -->
                    <div class="form-group">
                        <label for="exampleInputPassword" class="form-label">Password</label>
                        <input type="password" name="password" class="form-input"
                            id="exampleInputPassword" placeholder="Masukkan Password" required>
                        @error('password')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
    
                    <!-- Remember Me Checkbox -->
                    <div class="form-group remember-me">
                        <input type="checkbox" id="customCheck" name="remember">
                        <span style="font-size: 14px;">Ingatkan Saya</span>
                    </div>
    
                    <!-- Login Button -->
                    <button type="submit" class="btn-login">Masuk</button>
                </form>
    
                <div class="link-container">
                    <a href="{{ route('register') }}">Belum Punya Akun? Register</a>
                    {{-- <a href="/">Kembali ke Home</a> --}}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
