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
        /* Style for action buttons to be closer */
        .action-buttons .btn {
            margin-right: 5px; /* Adjust spacing between buttons */
        }
        .action-buttons .btn:last-child {
            margin-right: 0;
        }
    </style>
</head>

<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Data Nilai SKK Siswa</h4>
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
                            <th>NISN</th>
                            <th>Kelas</th>
                            <th>Nama Pembina</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($siswasWithPembinaInfo as $siswa)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $siswa->siswa_nama }}</td>
                                <td>{{ $siswa->nisn }}</td>
                                <td>{{ $siswa->kelas }}</td>
                                <td>{{ $siswa->last_pembina_name ?? 'Belum Dinilai' }}</td>
                                <td class="action-buttons">
                                    {{-- 1. Tambah (Plus) --}}
                                    <a href="{{ route('nilai_skk.create', [
                                        'siswa_id' => $siswa->siswa_id,
                                        'siswa_nama' => $siswa->siswa_nama,
                                        'siswa_nisn' => $siswa->nisn,
                                        'siswa_kelas' => $siswa->kelas,
                                    ]) }}" class="btn btn-primary btn-sm" title="Tambah Penilaian SKK Baru">
                                        <i class="fa fa-plus-square" aria-hidden="true"></i>
                                    </a>

                                    {{-- 2. Show (Eye) - Leads to Student's SKK Assessments Page --}}
                                    <a href="{{ route('nilai_skk.student_assessments', ['siswa_id' => $siswa->siswa_id]) }}" class="btn btn-info btn-sm" title="Lihat Semua Penilaian SKK Siswa">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- 3. Edit (Edit Icon) - Also Leads to Student's SKK Assessments Page --}}
                                    {{-- The actual editing for a specific SKK will happen on that page --}}
                                    <a href="{{ route('nilai_skk.student_assessments', ['siswa_id' => $siswa->siswa_id]) }}" class="btn btn-warning btn-sm" title="Kelola/Edit Penilaian SKK Siswa">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    {{-- 4. Delete (Trash) - Deletes ALL SKK Assessments for this student --}}
                                    <form action="{{ route('nilai_skk.delete_all_for_siswa', ['siswa_id' => $siswa->siswa_id]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus SEMUA penilaian SKK untuk siswa {{ $siswa->siswa_nama }}? Tindakan ini tidak dapat dibatalkan.')" title="Hapus Semua Penilaian SKK Siswa">
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

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
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
@endsection