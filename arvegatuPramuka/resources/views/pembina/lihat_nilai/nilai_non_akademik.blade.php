<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <style>
      /* Input search styling */
      .column-search {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
      }
 
      .column-search:focus {
        border-color: #66afe9;
        outline: none;
        box-shadow: 0 0 5px rgba(102, 175, 233, 0.6);
      }
    </style>
</head>

@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Daftar Nilai Non-Akademik Siswa</h4>
            <div class="d-flex ms-auto">
              {{-- Tombol baru untuk Export Data --}}
              <a href="{{ route('lihat_nilai.export_nilai_non_akademik') }}" class="btn btn-success btn-sm" title="Export Data">
                  <i class="fas fa-file-excel"></i> 
              </a>
            </div>
        </div> 
      <div class="card-body">
        <div class="table-responsive">
          <table id="multi-filter-select" class="display table table-striped table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>NISN</th>
                <th>Nama Siswa</th>
                @foreach ($categories as $category)
                    <th>{{ $category }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
                {{-- Data akan diisi oleh DataTables menggunakan properti 'data' dari controller --}}
            </tbody>
          </table>
        </div>
      </div>
    </div>
</div>
@endsection

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        // Kolom dinamis untuk DataTables
        var dynamicColumns = [
            { data: 'no', title: 'No' },
            { data: 'nisn', title: 'NISN' },
            { data: 'nama_siswa', title: 'Nama Siswa' },
        ];

        @foreach ($categories as $category)
            dynamicColumns.push({ data: '{{ strtolower(str_replace(' ', '_', $category)) }}', title: '{{ $category }}' });
        @endforeach

        // Inisialisasi DataTables
        var table = $('#multi-filter-select').DataTable({
            data: @json($data), // Pastikan variabel $data berisi array objek untuk DataTables
            columns: dynamicColumns,
            orderCellsTop: true, // Penting untuk header pencarian
            fixedHeader: true,   // Header tetap saat scroll
            pageLength: 5,       // Default jumlah entri per halaman
        });
    });
</script>
