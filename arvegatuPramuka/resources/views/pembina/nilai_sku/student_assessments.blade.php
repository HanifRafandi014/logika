@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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

        /* Sleek Progress Bar Styles */
        .progress-container {
            width: 100%;
            background-color: #f0f0f0;
            border-radius: 5px;
            height: 8px;
            overflow: hidden;
            position: relative;
            margin-bottom: 5px;
        }

        .progress-bar {
            height: 100%;
            background-color: #3490dc;
            border-radius: 5px;
            width: 0%;
            transition: width 0.3s ease-in-out;
            position: absolute;
            left: 0;
            top: 0;
        }

        .progress-text {
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>

<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Daftar Penilaian SKU untuk {{ $siswa->nama }}</h4>
            <a href="{{ route('nilai_sku.index') }}" class="btn btn-secondary btn-sm" title="Kembali ke Daftar Siswa">
                <i class="fa fa-arrow-left" aria-hidden="true"></i> Kembali
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
                <table id="grouped-sku-assessments-table" class="display table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Pembina</th>
                            <th>Tingkatan</th>
                            <th>Tanggal</th>
                            <th>Progress</th>
                            <th>Status SKU</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penilaianSkusGrouped as $group)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $group->siswa_nama }}</td>
                                <td>{{ $group->pembina_nama }}</td>
                                <td>{{ ucfirst($group->tingkatan) }}</td>
                                <td>{{ \Carbon\Carbon::parse($group->last_assessment_date)->translatedFormat('d F Y') }}</td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: {{ $group->progress_percentage }}%;"></div>
                                    </div>
                                    <div class="progress-text">{{ $group->checked_count }}/{{ $group->total_possible_items }} ({{ round($group->progress_percentage) }}%)</div>
                                </td>
                                <td>
                                    @if ($group->overall_status)
                                        <span class="badge bg-success">Lulus</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Belum Lulus</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- Tombol Detail untuk kelompok siswa-tingkatan ini --}}
                                    <a href="{{ route('nilai_sku.show_group', ['siswa_id' => $group->siswa_id, 'tingkatan' => $group->tingkatan]) }}" class="btn btn-sm btn-info" title="Detail Penilaian">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- Tombol Edit untuk kelompok siswa-tingkatan ini --}}
                                    <a href="{{ route('nilai_sku.edit_group', ['siswa_id' => $group->siswa_id, 'tingkatan' => $group->tingkatan]) }}" class="btn btn-sm btn-warning" title="Edit Penilaian">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    {{-- Tombol Hapus untuk kelompok siswa-tingkatan ini --}}
                                    <form action="{{ route('nilai_sku.destroy_group', ['siswa_id' => $group->siswa_id, 'tingkatan' => $group->tingkatan]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus semua penilaian SKU untuk {{ $group->siswa_nama }} pada tingkatan {{ ucfirst($group->tingkatan) }}?')" title="Hapus Penilaian">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada penilaian SKU untuk siswa ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#grouped-sku-assessments-table').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5,
        });
    });
</script>
@endsection