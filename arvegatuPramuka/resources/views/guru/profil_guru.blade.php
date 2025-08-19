@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.guru')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card">
        <div class="card-header">
            <h4>Data Profil Guru</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('guru.profil.update') }}">
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
                           value="{{ old('nama', $guru->nama ?? '') }}">
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
                           value="{{ old('kelas', $guru->kelas ?? '') }}">
                    @error('kelas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="mata_pelajaran">Mata Pelajaran</label>
                    <input type="text" 
                           class="form-control @error('mata_pelajaran') is-invalid @enderror" 
                           id="mata_pelajaran" 
                           name="mata_pelajaran" 
                           value="{{ old('mata_pelajaran', $guru->mata_pelajaran ?? '') }}">
                    @error('mata_pelajaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="nip">NIP</label>
                    <input type="text" 
                           class="form-control @error('nip') is-invalid @enderror" 
                           id="nip" 
                           name="nip" 
                           value="{{ old('nip', $guru->nip ?? '') }}">
                    @error('nip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="pembina_pramuka">Pembina Pramuka</label>
                    <select class="form-control @error('pembina_pramuka') is-invalid @enderror" 
                            id="pembina_pramuka" 
                            name="pembina_pramuka">
                        <option value="0" {{ old('pembina_pramuka', $guru->pembina_pramuka ?? '') == '0' ? 'selected' : '' }}>Tidak</option>
                        <option value="1" {{ old('pembina_pramuka', $guru->pembina_pramuka ?? '') == '1' ? 'selected' : '' }}>Ya</option>
                    </select>
                    @error('pembina_pramuka')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" title="Simpan">
                    <i class="fas fa-save"></i>
                </button>
                <a href="{{ route('guru.dashboard') }}" class="btn btn-secondary" title="Kembali">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
