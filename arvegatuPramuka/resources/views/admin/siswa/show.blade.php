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

        .student-info-container {
            background-color: #f8f9fa; /* Warna latar belakang ringan */
            border-radius: 8px; /* Sudut membulat */
            padding: 20px; /* Padding di sekitar konten */
            margin-bottom: 20px; /* Jarak bawah */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); /* Sedikit bayangan untuk kesan mendalam */
        }

        .student-info-details {
            display: flex; /* Menggunakan flexbox untuk tata letak yang rapi */
            flex-wrap: wrap; /* Memungkinkan wrap ke baris berikutnya pada layar kecil */
            gap: 20px; /* Jarak antar item */
            padding-bottom: 30px;
        }

        .student-info-item {
            flex: 1 1 auto; /* Fleksibel, ambil ruang yang tersedia, bisa menyusut */
            min-width: 150px; /* Lebar minimum untuk setiap item */
            font-size: 1rem; /* Ukuran font normal */
            color: #6c757d; /* Warna teks abu-abu */
        }

        .student-info-item strong {
            color: #495057; /* Warna teks lebih gelap untuk label */
            margin-right: 5px; /* Jarak antara label dan nilai */
        }

        /* Card styling for clickable sections */
        .grade-card {
            cursor: pointer;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .grade-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .grade-card.active {
            border-color: #007bff; /* Highlight active card */
            background-color: #e7f3ff;
        }

        .table-container {
            display: none; /* Hidden by default */
            margin-top: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden; /* To keep border-radius on table if it overflows */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 15px; /* Add some padding inside the container */
        }

        .table-container.active {
            display: block; /* Show when active */
        }
    </style>
</head>

@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-12">
    <div class="container">
        <h4 class="table-title" style="text-align: center; margin-bottom: 30px;">Detail Data & Nilai Siswa</h4>

        <div class="student-info-container">
            <h5 style="margin-bottom: 20px; color: #333;">Informasi Siswa</h5>
            <div class="student-info-details">
                <div class="student-info-item">
                    <strong>Nama Siswa:</strong> {{ $siswa->nama ?? 'N/A' }}
                </div>
                <div class="student-info-item">
                    <strong>NISN:</strong> {{ $siswa->nisn ?? 'N/A' }}
                </div>
                <div class="student-info-item">
                    <strong>Kelas:</strong> {{ $siswa->kelas ?? 'N/A' }}
                </div>
                <div class="student-info-item">
                    <strong>Angkatan:</strong> {{ $siswa->angkatan ?? 'N/A' }}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="grade-card" id="academic-card">
                    <h5 class="mb-0">Nilai Akademik</h5>
                    <p class="text-muted">Klik untuk melihat detail nilai akademik.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="grade-card" id="non-academic-card">
                    <h5 class="mb-0">Nilai Non-Akademik</h5>
                    <p class="text-muted">Klik untuk melihat detail nilai non-akademik.</p>
                </div>
            </div>
        </div>

        {{-- Academic Grades Table --}}
        <div class="table-container" id="academic-grades-table">
            <h5 style="margin-bottom: 15px; color: #333;">Tabel Nilai Akademik</h5>
            @if ($siswa->nilai_akademik && $siswa->nilai_akademik->isNotEmpty())
                <div class="table-responsive">
                    <table id="academicDataTable" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Mata Pelajaran</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($siswa->nilai_akademik as $nilai)
                            <tr>
                                <td>{{ $nilai->mata_pelajaran }}</td>
                                <td>{{ $nilai->nilai }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p>Tidak ada nilai akademik yang tersedia.</p>
            @endif
        </div>

        {{-- Non-Academic Grades Table --}}
        <div class="table-container" id="non-academic-grades-table">
            <h5 style="margin-bottom: 15px; color: #333;">Tabel Nilai Non-Akademik</h5>
            @if ($siswa->nilai_non_akademik && $siswa->nilai_non_akademik->isNotEmpty())
                <div class="table-responsive">
                    <table id="nonAcademicDataTable" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($siswa->nilai_non_akademik as $nilai)
                            <tr>
                                <td>{{ $nilai->kategori }}</td>
                                <td>{{ $nilai->nilai }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p>Tidak ada nilai non-akademik yang tersedia.</p>
            @endif
        </div>

        <a href="{{ route('data-siswa.index') }}" class="btn btn-secondary btn-sm mt-4" title="Kembali">
            <i class="fas fa-arrow-left"></i> 
        </a>
    </div>
</div>
@endsection

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        // Initialize DataTables for both tables
        var academicTable = $('#academicDataTable').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5
        });

        var nonAcademicTable = $('#nonAcademicDataTable').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5
        });

        // Hide both tables initially
        $('#academic-grades-table').hide();
        $('#non-academic-grades-table').hide();

        // Handle card clicks
        $('#academic-card').on('click', function() {
            $('#academic-grades-table').slideDown();
            $('#non-academic-grades-table').slideUp();
            $('.grade-card').removeClass('active');
            $(this).addClass('active');
            academicTable.columns.adjust().draw(); // Redraw DataTables to fix header/column issues
        });

        $('#non-academic-card').on('click', function() {
            $('#non-academic-grades-table').slideDown();
            $('#academic-grades-table').slideUp();
            $('.grade-card').removeClass('active');
            $(this).addClass('active');
            nonAcademicTable.columns.adjust().draw(); // Redraw DataTables to fix header/column issues
        });

        // Optional: Show academic table by default on page load
        $('#academic-card').click();
    });
</script>