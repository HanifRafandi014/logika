<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Arvegatu</title>
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
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: black;
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

    /* Kotak Form */
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

    /* Form Input dan Select */
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

    /* Link bawah */
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
                <h2 class="login-title">Register Arvegatu</h2>
    
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
    
                <form method="POST" action="{{ route('register.attempt') }}" class="user" id="registerForm">
                    @csrf
    
                    <!-- Username -->
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username"
                            class="form-input"
                            placeholder="Masukkan Username" value="{{ old('username') }}" required>
                        @error('username')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
    
                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password"
                            class="form-input"
                            placeholder="Masukkan Password" required>
                        @error('password')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
    
                    <!-- Role -->
                    <div class="form-group">
                        <label for="role-select" class="form-label">Pilih Role</label>
                        <select name="role" id="role-select"
                            class="form-input" required>
                            <option value="">Select Role</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="pembina" {{ old('role') == 'pembina' ? 'selected' : '' }}>Pembina</option>
                            <option value="siswa" {{ old('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="alumni" {{ old('role') == 'alumni' ? 'selected' : '' }}>Alumni</option>
                            <option value="guru" {{ old('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="orang_tua" {{ old('role') == 'orang_tua' ? 'selected' : '' }}>Orang Tua</option>
                        </select>
                        @error('role')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
    
                    <!-- Button -->
                    <button type="submit" class="btn-login">Buat Akun</button>
                </form>
    
                <div class="link-container">
                    <a href="{{ route('login') }}">Sudah Punya Akun? Login Sekarang</a>
                </div>
            </div>
        </div>      
    </div>
</body>
</html>
