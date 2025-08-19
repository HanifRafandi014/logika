@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Sleek Progress Bar Styles (copied from index) */
        .progress-container {
            width: 100%;
            background-color: #f0f0f0; /* Light grey background */
            border-radius: 5px;
            height: 8px; /* Reduced height for a sleeker look */
            overflow: hidden;
            position: relative;
            margin-bottom: 5px; /* Space between bar and text */
        }

        .progress-bar {
            height: 100%;
            background-color: #3490dc; /* Blue color like the example */
            border-radius: 5px;
            width: 0%; /* Initial width */
            transition: width 0.3s ease-in-out;
            position: absolute;
            left: 0;
            top: 0;
        }

        .progress-text {
            font-size: 0.9em;
            color: #555;
        }

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
            {{-- UPDATED TITLE --}}
            <h4 class="card-title mb-0">Daftar Penilaian SKK untuk {{ $siswa->nama }}</h4>
            {{-- UPDATED BACK BUTTON LINK --}}
            <a href="{{ route('nilai_skk.index') }}" class="btn btn-secondary btn-sm" title="Kembali ke Daftar Siswa">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
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
                {{-- Updated table ID for clarity --}}
                <table id="grouped-skk-assessments-table" class="display table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tingkatan</th>
                            <th>Jenis SKK</th>
                            <th>Tanggal Penilaian</th>
                            <th>Progress</th>
                            <th>Status SKK</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Make sure $penilaianSkksGrouped is passed from controller (it is in studentAssessments method) --}}
                        @forelse ($penilaianSkksGrouped as $group)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ ucfirst($group->tingkatan) }}</td>
                                <td>{{ $group->jenis_skk }}</td>
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
                                    {{-- These links remain the same, they point to the specific SKK group actions --}}
                                    <a href="{{ route('nilai_skk.show_group', [
                                        'siswa_id' => $group->siswa_id,
                                        'tingkatan' => $group->tingkatan,
                                        'jenis_skk' => $group->jenis_skk
                                    ]) }}" class="btn btn-sm btn-info" title="Detail Penilaian SKK ini">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('nilai_skk.edit_group', [
                                        'siswa_id' => $group->siswa_id,
                                        'tingkatan' => $group->tingkatan,
                                        'jenis_skk' => $group->jenis_skk
                                    ]) }}" class="btn btn-sm btn-warning" title="Edit Penilaian SKK ini">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('nilai_skk.destroy_group', [
                                        'siswa_id' => $group->siswa_id,
                                        'tingkatan' => $group->tingkatan,
                                        'jenis_skk' => $group->jenis_skk
                                    ]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus penilaian Skk untuk {{ $group->siswa_nama }} pada tingkatan {{ ucfirst($group->tingkatan) }} dan jenis SKK {{ $group->jenis_skk }}?')" title="Hapus Penilaian SKK ini">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada penilaian SKK untuk siswa ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#grouped-skk-assessments-table').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5,
        });
    });
</script>
@endsection