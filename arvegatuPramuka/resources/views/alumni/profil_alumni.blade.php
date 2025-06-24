<head>
    <title>Data Profil Alumni</title>
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
    @include('layouts.sidebar.alumni')
@endsection
@section('content')
<div class="col-md-12" style="font-size: 11px;">
    <div class="row justify-content-center">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 style="text-align: center;">Data Profil Alumni</h4>
            </div>
            <form method="POST" action="{{ route('alumni.profil.update') }}" style="padding-top: 30px;">
                @csrf
                {{-- Gunakan @method('PUT') karena ini adalah operasi update --}}
                @method('PUT') 

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="form-group">
                    <label for="nama">Nama Alumni</label>
                    {{-- Tambahkan 'readonly' agar field ini tidak bisa diedit --}}
                    <input type="text" class="form-control" id="nama" name="nama" value="{{ $alumni->siswa->nama ?? '' }}" readonly>
                </div>
                <div class="form-group">
                    <label for="tahun_lulus">Tahun Lulus</label>
                    <input type="text" class="form-control" id="tahun_lulus" name="tahun_lulus" value="{{ $alumni->tahun_lulus ?? '' }}">
                </div>
                <div class="form-group">
                    <label for="pekerjaan">Pekerjaan</label>
                    <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" value="{{ $alumni->pekerjaan ?? '' }}">
                </div>
                <div class="form-group">
                    <label for="no_hp">No HP</label>
                    <input type="text" class="form-control" id="no_hp" name="no_hp" value="{{ $alumni->no_hp ?? '' }}">
                </div>
                
                <button type="submit" class="btn btn-primary" title="Simpan">
                    <i class="fa fa-bookmark" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </div>    
</div>
@endsection
