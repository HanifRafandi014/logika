<head>
    <title>Data Profil Orang Tua</title>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            margin-top: 50px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>

@extends('layouts.main')
@section('sidebar')
    @include('layouts.sidebar.orang_tua')
@endsection
@section('content')
<div class="col-md-12">
    <div class="row justify-content-center">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 style="text-align: center;">Data Profil Orang Tua</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('orang-tua.profile.save-update') }}" method="POST" style="padding-top: 30px;">
                        @csrf
                        @if($orangTua)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama', $orangTua->nama ?? '') }}" required>
                            @error('nama')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="no_hp" class="form-label">No HP</label>
                            <input type="text" class="form-control" id="no_hp" name="no_hp" value="{{ old('no_hp', $orangTua->no_hp ?? '') }}" required>
                            @error('no_hp')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required>{{ old('alamat', $orangTua->alamat ?? '') }}</textarea>
                            @error('alamat')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="siswa_id" class="form-label">Nama Siswa</label>
                            <select class="form-control" id="siswa_id" name="siswa_id"> {{-- Hapus 'required' jika siswa_id nullable --}}
                                <option value="">Pilih Siswa</option>
                                {{-- Loop melalui SEMUA siswa yang didapatkan dari controller --}}
                                @foreach ($siswas as $siswa)
                                    <option value="{{ $siswa->id }}"
                                        {{ (old('siswa_id', $orangTua->siswa_id ?? '') == $siswa->id) ? 'selected' : '' }}>
                                        {{ $siswa->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('siswa_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="username_login" class="form-label">Username Login</label>
                            <input type="text" class="form-control" id="username_login" value="{{ $loggedInUsername }}" readonly>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> {{ $orangTua ? 'Update Data' : 'Simpan Data' }}
                        </button>
                    </form>
                </div>
            </div>
    </div>
</div>
@endsection