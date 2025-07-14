<head>
    <title>Data Profil Pembina</title>
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
    @include('layouts.sidebar.pembina')
@endsection
@section('content')
<div class="col-md-12" style="font-size: 11px;">
    <div class="row justify-content-center">
        <div class="card">
            <div class="card-header bg-primary text-white">
                    <h4 style="text-align: center;">Data Profil Pembina</h4>
                </div>
            <form method="POST" action="{{ route('pembina.profil.update') }}" style="padding-top: 30px;">
                @csrf

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

                <div class="mb-3">
                    <label for="nama">Nama</label>
                    <input type="text" class="form-control" id="nama" name="nama" value="{{ $pembina->nama ?? '' }}">
                </div>
                <div class="mb-3">
                    <label for="kelas">Kelas</label>
                    <input type="text" class="form-control" id="kelas" name="kelas" value="{{ $pembina->kelas ?? '' }}">
                </div>
                <div class="mb-3">
                    <label for="kategori">Kategori</label>
                    <input type="text" class="form-control" id="kategori" name="kategori" value="{{ $pembina->kategori ?? '' }}">
                </div>
                <div class="mb-3">
                    <label for="nip">NIP</label>
                    <input type="text" class="form-control" id="nip" name="nip" value="{{ $pembina->nip ?? '' }}">
                </div>
                <button type="submit" class="btn btn-primary" title="Simpan">
                    <i class="fa fa-bookmark" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </div>    
</div>
@endsection