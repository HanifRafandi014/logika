<head>
    <title>Daftar Lomba Rekomendasi</title>
    <style>
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none; /* Untuk link */
            color: #333; /* Warna teks default */
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .card h3 {
            margin: 0;
            font-size: 1.2em;
            color: #007bff; /* Warna judul card */
        }
        .alert-message {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .alert-info {
            color: #31708f;
            background-color: #d9edf7;
            border-color: #bce8f1;
        }
    </style>
</head>

@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
    <h1>Pilih Lomba untuk Melihat Rekomendasi Siswa</h1>

    @if (isset($error))
        <div class="alert-message alert-danger">
            {{ $error }}
        </div>
    @endif
    @if (isset($message))
        <div class="alert-message alert-info">
            {{ $message }}
        </div>
    @endif

    <div class="card-container">
        @foreach ($allLombas as $slug => $displayName)
            <a href="{{ route('pembina.rekomendasi.showByLomba', ['lombaSlug' => $slug]) }}" class="card">
                <h3>{{ $displayName }}</h3>
            </a>
        @endforeach
    </div>
@endsection