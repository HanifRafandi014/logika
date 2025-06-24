@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <style>
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

<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Data Nilai SKU</h4>
            <a href="{{ route('nilai_sku.create') }}" class="btn btn-primary btn-sm me-2" title="Tambah Nilai SKU">
                <i class="fa fa-plus-square" aria-hidden="true"></i>
            </a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table id="multi-filter-select" class="display table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Pembina</th>
                            <th>Tingkatan</th>
                            <th>Tanggal</th>
                            <th>Status SKU</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($penilaianSkusGrouped as $group)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $group->siswa_nama }}</td>
                                <td>{{ $group->pembina_nama }}</td>
                                <td>{{ ucfirst($group->tingkatan) }}</td>
                                <td>{{ \Carbon\Carbon::parse($group->last_assessment_date)->translatedFormat('d F Y') }}</td>
                                <td>
                                    @if ($group->overall_status)
                                        <span class="badge bg-success">Lulus</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Belum Lulus</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('nilai_sku.edit_group', ['siswa_id' => $group->siswa_id, 'tingkatan' => $group->tingkatan]) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('nilai_sku.destroy_group', ['siswa_id' => $group->siswa_id, 'tingkatan' => $group->tingkatan]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus semua penilaian SKU untuk {{ $group->siswa_nama }} pada tingkatan {{ ucfirst($group->tingkatan) }}?')" title="Hapus">
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

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#multi-filter-select').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 10
        });
    });
</script>
@endsection
