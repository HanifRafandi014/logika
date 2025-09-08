@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card">
        <div class="card-header">
            <h4>Data Profil Pembina</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('pembina.profil.update') }}">
                @csrf

                {{-- Alert error --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Alert success --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input type="text" 
                           class="form-control @error('nama') is-invalid @enderror" 
                           id="nama" 
                           name="nama" 
                           value="{{ old('nama', $pembina->nama ?? '') }}">
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="kelas">Kelas</label>
                    <input type="text" 
                           class="form-control @error('kelas') is-invalid @enderror" 
                           id="kelas" 
                           name="kelas" 
                           value="{{ old('kelas', $pembina->kelas ?? '') }}">
                    @error('kelas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <input type="text" 
                           class="form-control @error('kategori') is-invalid @enderror" 
                           id="kategori" 
                           name="kategori" 
                           value="{{ old('kategori', $pembina->kategori ?? '') }}">
                    @error('kategori')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="nip">NIP</label>
                    <input type="text" 
                           class="form-control @error('nip') is-invalid @enderror" 
                           id="nip" 
                           name="nip" 
                           value="{{ old('nip', $pembina->nip ?? '') }}">
                    @error('nip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" title="Simpan">
                    <i class="fas fa-save"></i>
                </button>
                <a href="{{ route('pembina.dashboard') }}" class="btn btn-secondary" title="Kembali">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
