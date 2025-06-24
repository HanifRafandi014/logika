<head>
    <title>Rekomendasi untuk {{ $lombaName ?? 'Lomba' }}</title>
    {{-- Memastikan DataTables CSS dan JS dimuat dengan benar --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        /* Styling untuk tombol kembali */
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.2s;
        }
        .back-button:hover {
            background-color: #5a6268;
        }
        /* Tambahkan style untuk table jika diperlukan, mirip dengan sebelumnya */
        table.dataTable thead th {
            background-color: #f2f2f2;
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
    <h1>Rekomendasi Siswa untuk Lomba: {{ $lombaName }}</h1>

    <a href="{{ route('pembina.rekomendasi.index') }}" class="back-button">← Kembali ke Daftar Lomba</a>

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

    @if (!empty($rekomendasi))
        <p>Dibutuhkan: {{ $requiredNum }} Siswa</p>
        <table border="1" class="display" id="rekomendasiTable">
            <thead>
                <tr>
                    <th>ID Siswa</th>
                    <th>Nama Siswa</th>
                    <th>Kategori Cluster</th>
                    <th>Rata-rata Skor Lomba</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rekomendasi as $rec)
                    <tr>
                        <td>{{ $rec['ID Siswa'] }}</td>
                        <td>{{ $rec['Nama Siswa'] }}</td>
                        <td>{{ $rec['Kategori Cluster'] }}</td>
                        <td>{{ number_format($rec['Rata-rata Skor Lomba'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Tidak ada rekomendasi siswa yang tersedia untuk lomba {{ $lombaName }}.</p>
    @endif
@endsection

<script>
    $(document).ready(function () {
        // Inisialisasi DataTables untuk tabel rekomendasi
        $('#rekomendasiTable').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5,
        });
    });
</script>