<head>
    <title>Rekomendasi untuk {{ $lombaName ?? 'Lomba' }}</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
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
        .btn-success {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .btn-success:hover {
            background-color: #218838;
        }
    </style>
</head>

@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
    <h1>Rekomendasi Siswa untuk Lomba: {{ $lombaName }}</h1>

    @if (isset($error))
        <div class="alert-message alert-danger">
            {{ $error }}
        </div>
    @endif

    @if (session('message'))
        <div class="alert-message alert-info">
            {{ session('message') }}
        </div>
    @endif

    @if (isset($message))
        <div class="alert-message alert-info">
            {{ $message }}
        </div>
    @endif

    @if (!empty($rekomendasi))
        <p>Dibutuhkan: {{ $requiredNum }} Siswa</p>

        {{-- Bar tombol & status dalam 1 baris --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap;">
            {{-- Tombol Kembali --}}
            <div>
                <a href="{{ route('pembina.rekomendasi.index') }}" class="back-button" title="Kembali">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>

            {{-- Status Terakhir Disimpan --}}
            @php
                $lastSaved = \App\Models\HasilClustering::where('kategori_lomba', $lombaName)->latest('updated_at')->first();
            @endphp
            <div style="text-align: center; flex: 1;">
                @if ($lastSaved)
                    <p style="margin: 0; font-weight: bold; color: #555;">🕒 Terakhir disimpan: {{ $lastSaved->updated_at->format('d M Y') }}</p>
                @else
                    <p style="margin: 0; font-weight: bold; color: #888;">Belum ada penyimpanan data</p>
                @endif
            </div>

            {{-- Tombol Simpan --}}
            <div>
                <form method="POST" action="{{ route('pembina.rekomendasi.save', ['lombaSlug' => \Str::slug($lombaName)]) }}">
                    @csrf
                    @foreach ($rekomendasi as $rec)
                        <input type="hidden" name="rekomendasi[]" value="{{ json_encode($rec) }}">
                    @endforeach
                    <button type="submit" class="btn btn-primary me-2" title="Simpan Rekomendasi">
                        <i class="fas fa-save"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- Tabel --}}
        <table border="1" class="display" id="rekomendasiTable">
            <thead>
                <tr>
                    <th>ID Siswa</th>
                    <th>Nama Siswa</th>
                    <th>Jenis Kelamin</th>
                    <th>Kategori Cluster</th>
                    <th>Rata-rata Skor Lomba</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rekomendasi as $rec)
                    @php
                        $siswa = \App\Models\Siswa::where('nama', $rec['Nama Siswa'])->first();
                    @endphp
                    <tr>
                        <td>{{ $rec['ID Siswa'] }}</td>
                        <td>{{ $rec['Nama Siswa'] }}</td>
                        <td>
                      @if ($siswa?->jenis_kelamin == 1)
                          Laki-laki
                      @else
                          Perempuan
                      @endif
                  </td>
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
        $('#rekomendasiTable').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5,
        });
    });
</script>
