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
            <h4 class="card-title">Manajemen Clustering Regu Inti</h4>
            <a href="{{ route('lomba.create') }}" class="btn btn-primary btn-sm" title="Tambah Lomba">
                <i class="fa fa-plus-square" aria-hidden="true"></i>
            </a>
        </div>
        <div class="card-body">

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
                <table id="multi-filter-select" class="display table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jenis Lomba</th>
                            <th>Jumlah Siswa</th>
                            <th>Variabel Akademik</th>
                            <th>Variabel Non-Akademik</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lombas as $index => $lomba)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $lomba->variabel->jenis_lomba ?? '-' }}</td>
                                <td>{{ $lomba->jumlah_siswa }}</td>
                                <td>
                                    @if (!empty($lomba->variabel?->variabel_akademiks))
                                        <ul class="mb-0">
                                            @foreach ($lomba->variabel->variabel_akademiks as $ak)
                                                <li>{{ $ak }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($lomba->variabel?->variabel_non_akademiks))
                                        <ul class="mb-0">
                                            @foreach ($lomba->variabel->variabel_non_akademiks as $nak)
                                                <li>{{ $nak }}</li>
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
    $(document).ready(function () {
        $('#multi-filter-select').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5,
        });
    });
</script>
