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
            <h4 class="card-title">Manajemen Data Lomba</h4>
            <a href="{{ route('lomba.create') }}" class="btn btn-primary btn-sm" title="Tambah Lomba">
                <i class="fa fa-plus-square" aria-hidden="true"></i>
            </a>
        </div>
        <div class="card-body">
            {{-- Pesan sukses atau error --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="lomba-table" class="display table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jenis Lomba</th>
                            <th>Jumlah Siswa</th>
                            <th>Variabel Akademik </th> 
                            <th>Variabel Non-Akademik</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lombas as $index => $lomba)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $lomba->jenis_lomba }}</td>
                                <td>{{ $lomba->jumlah_siswa }}</td>
                                <td>
                                    @if ($lomba->related_nilai_akademiks->isNotEmpty())
                                        <ul>
                                            @foreach ($lomba->related_nilai_akademiks as $nilaiAkademik)
                                                <li>{{ $nilaiAkademik->mata_pelajaran }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($lomba->related_nilai_non_akademiks->isNotEmpty())
                                        <ul>
                                            @foreach ($lomba->related_nilai_non_akademiks as $nilaiNonAkademik)
                                                <li>{{ $nilaiNonAkademik->kategori }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($lomba->status)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('lomba.edit', $lomba->id) }}" class="btn btn-sm btn-warning" title="Edit Lomba">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('lomba.destroy', $lomba->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data lomba ini?')" title="Hapus Lomba">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#lomba-table').DataTable({
            "pageLength": 10, // Mengatur jumlah baris per halaman
            "columnDefs": [
                { "orderable": false, "targets": [0, 4] }, // Kolom No dan Aksi tidak dapat diurutkan (perlu disesuaikan jika jumlah kolom berubah)
                { "searchable": false, "targets": [0, 4] } // Kolom No dan Aksi tidak dapat dicari global (perlu disesuaikan)
            ]
        });
    });
</script>