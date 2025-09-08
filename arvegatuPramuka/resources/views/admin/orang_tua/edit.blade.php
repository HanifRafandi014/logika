@extends('layouts.main')

@section('sidebar')
  @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
  <div class="card">
    <div class="card-header">
      <h4>Edit Data Orang Tua</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('data-orang-tua.update', $orangTua->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group mb-3">
          <label for="nama">Nama Orang Tua</label>
          <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $orangTua->nama) }}" required>
          @error('nama')
              <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group mb-3">
          <label for="no_hp">No Handphone</label>
          <input type="text" name="no_hp" id="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $orangTua->no_hp) }}" required>
          @error('no_hp')
              <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group mb-3">
            <label for="alamat">Alamat</label>
            <input type="text" name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" value="{{ old('alamat', $orangTua->alamat) }}" required>
            @error('alamat')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Perubahan di sini: Menambahkan dropdown 'siswa_id' --}}
        <div class="form-group mb-3">
            <label for="siswa_id">Nama Siswa</label>
            <select class="form-control @error('siswa_id') is-invalid @enderror" id="siswa_id" name="siswa_id">
                <option value="">Pilih Siswa</option>
                @foreach($siswas as $siswa)
                    <option value="{{ $siswa->id }}" {{ (old('siswa_id', $orangTua->siswa_id) == $siswa->id) ? 'selected' : '' }}>
                        {{ $siswa->nama }}
                    </option>
                @endforeach
            </select>
            @error('siswa_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="">Pilih Status</option>
                @foreach($statuss as $statusOption) {{-- Ganti variabel $status agar tidak bentrok dengan model --}}
                    <option value="{{ $statusOption }}" {{ (old('status', $orangTua->status) == $statusOption) ? 'selected' : '' }}>{{ $statusOption }}</option>
                @endforeach
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group mb-3">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                    @error('username')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>
        <button type="submit" class="btn btn-primary" title="Update">
            <i class="fas fa-sync-alt"></i>
        </button>
        <a href="{{ route('data-orang-tua.index') }}" class="btn btn-secondary" title="Kembali">
            <i class="fas fa-arrow-left"></i>
        </a>
      </form>
    </div>
  </div>
</div>
@endsection
