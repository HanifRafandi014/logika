@extends('layouts.main')

@section('sidebar')
  @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
  <div class="card">
    <div class="card-header">
      <h4>Edit Data Guru</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('data-guru.update', $guru->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="container-md">
          <div class="form-group">
            <label for="nama">Nama Guru</label>
            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $guru->nama) }}" required>
            @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="mata_pelajaran">Mata Pelajaran</label>
            <input type="text" class="form-control @error('mata_pelajaran') is-invalid @enderror" id="mata_pelajaran" name="mata_pelajaran" value="{{ old('mata_pelajaran', $guru->mata_pelajaran) }}" required>
            @error('mata_pelajaran')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="nip">NIP</label>
            <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip" name="nip" value="{{ old('nip', $guru->nip) }}" required>
            @error('nip')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $guru->user->username) }}" required>
            @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="text" class="form-control @error('password') is-invalid @enderror" id="password" name="password" value="{{ old('password', $guru->password) }}">
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti password</small>
          </div>
          <div class="form-group">
            <label for="password_confirmation">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
          </div>
          <div class="form-group">
            <label for="pembina_pramuka">Pembina Pramuka</label>
            <select class="form-control @error('pembina_pramuka') is-invalid @enderror" id="pembina_pramuka" name="pembina_pramuka" required>
                <option value="">Pilih Status</option>
                <option value="1" {{ old('pembina_pramuka') == '1' ? 'selected' : '' }}>Ya</option>
                <option value="0" {{ old('pembina_pramuka') == '0' ? 'selected' : '' }}>Tidak</option>
            </select>
            @error('pembina_pramuka')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <button type="submit" class="btn btn-primary mr-2" title="Update">
            <i class="fas fa-sync-alt"></i>
          </button>
          <a href="{{ route('data-guru.index') }}" class="btn btn-secondary ml-2" title="Kembali">
            <i class="fas fa-arrow-left"></i>
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
