@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
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
                text-decoration: none;
                color: #333;
            }
            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            }
            .card h3 {
                margin: 0;
                font-size: 1.2em;
                color: #007bff;
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
            .action-buttons {
                margin-top: 30px;
                margin-bottom: 20px;
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            .action-buttons a {
                padding: 10px 15px;
                background-color: #007bff;
                color: white;
                border-radius: 5px;
                text-decoration: none;
                font-weight: 500;
                transition: background-color 0.3s ease;
            }
            .action-buttons a:hover {
                background-color: #0056b3;
            }
        </style>
    </head>


    {{-- Tampilkan notifikasi --}}
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

    <h1>Alur Proses Clustering</h1>

    {{-- Tombol tambahan untuk fitur lain --}}
    <div class="action-buttons">
        <a href="{{ route('pembina.rekomendasi.normalisasi') }}">📊 Normalisasi Data</a>
        <a href="{{ route('pembina.rekomendasi.status') }}">📋 Status Pemenuhan Lomba</a>
        <a href="{{ route('pembina.rekomendasi.ranking') }}">🏅 Perangkingan Siswa</a>
        <a href="{{ route('pembina.rekomendasi.detail_potensi') }}">📋 Detail Potensi</a>
        <a href="{{ route('pembina.rekomendasi.grafik') }}">📊 Grafik Visualisasi</a>
    </div>

    <h1 style="padding-top: 5%;">Pilih Kompetensi Siswa Sesuai Kebutuhan Lomba</h1>

    {{-- Daftar Lomba --}}
    <div class="card-container">
        @forelse ($allLombas as $slug => $displayName)
            <a href="{{ route('pembina.rekomendasi.showByLomba', ['lombaSlug' => $slug]) }}" class="card">
                <h3>{{ $displayName }}</h3>
            </a>
        @empty
            <p>Tidak ada lomba tersedia saat ini.</p>
        @endforelse
    </div>
@endsection
