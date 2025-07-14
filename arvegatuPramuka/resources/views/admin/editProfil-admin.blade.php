@extends('layouts.main')
@section('sidebar')
    @include('layouts.sidebar.admin')
@endsection
@section('content')
<div class="container">
    <h2>Edit Profil</h2>
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    <form action="{{ route('updateProfilAdmin') }}" method="POST" enctype="multipart/form-data" onsubmit="return validatePasswords()">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="{{ $user->username }}" required>
        </div>

        <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" class="form-control" id="nama" name="nama" value="{{ $admin->nama }}" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password Baru</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
        </div>

        <div class="mb-3">
            <label for="foto_profil" class="form-label">Foto Profil</label>
            <input type="file" class="form-control" id="foto_profil" name="foto_profil">
            <small class="form-text text-muted">Format yang diizinkan: JPEG, PNG, JPG, GIF, SVG. Ukuran maksimal: 2MB.</small>
        </div>

        <button type="submit" class="btn btn-primary me-2" style="font-size: 11px;" title="Update">
            <i class="fas fa-sync-alt"></i>
        </button>
        <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="btn btn-secondary ms-2" style="font-size: 11px;" title="Kembali">
            <i class="fas fa-arrow-left"></i>
        </a>
    </form>
</div>

<script>
    function validatePasswords() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password_confirmation').value;

        if (password !== confirmPassword) {
            alert('Confirm Password harus sama dengan New Password!');
            return false;
        }
        return true;
    }
</script>
@endsection