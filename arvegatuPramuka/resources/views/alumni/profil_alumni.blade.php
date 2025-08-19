@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.alumni')
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card">
        <div class="card-header">
            <h4>Data Profil Alumni</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('alumni.profil.update') }}">
                @csrf
                @method('PUT')

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
                    <label for="nama">Nama Alumni</label>
                    <input type="text" 
                           class="form-control" 
                           id="nama" 
                           name="nama" 
                           value="{{ $alumni->siswa->nama ?? '' }}" 
                           readonly>
                </div>

                <div class="form-group">
                    <label for="tahun_lulus">Tahun Lulus</label>
                    <input type="text" 
                           class="form-control @error('tahun_lulus') is-invalid @enderror" 
                           id="tahun_lulus" 
                           name="tahun_lulus" 
                           value="{{ old('tahun_lulus', $alumni->tahun_lulus ?? '') }}">
                    @error('tahun_lulus')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="pekerjaan">Pekerjaan</label>
                    <input type="text" 
                           class="form-control @error('pekerjaan') is-invalid @enderror" 
                           id="pekerjaan" 
                           name="pekerjaan" 
                           value="{{ old('pekerjaan', $alumni->pekerjaan ?? '') }}">
                    @error('pekerjaan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="no_hp">No HP</label>
                    <input type="text" 
                           class="form-control @error('no_hp') is-invalid @enderror" 
                           id="no_hp" 
                           name="no_hp" 
                           value="{{ old('no_hp', $alumni->no_hp ?? '') }}">
                    @error('no_hp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" title="Simpan">
                    <i class="fa fa-bookmark" aria-hidden="true"></i>
                </button>
                <a href="{{ route(name: 'alumni.dashboard') }}" class="btn btn-secondary" title="Kembali">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </form>
        </div>
    </div>
</div>
@endsection
