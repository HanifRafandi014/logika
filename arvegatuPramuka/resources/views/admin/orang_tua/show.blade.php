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
    </style>
  </head>

@extends('layouts.main')

@section('sidebar')
  @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-12">
  <div class="table-container">
    <h4 class="table-title" style="text-align: center;">Tabel Data Siswa</h4>
    <div class="student-info-details">
                <div class="student-info-item">
                    <strong>Nama Orang Tua:</strong> {{ $orangTua->nama ?? 'N/A' }}
                </div>
                <div class="student-info-item">
                    <strong>Status:</strong> {{ $orangTua->status ?? 'N/A' }}
                </div>
                <div class="student-info-item">
                    <strong>Nama Siswa:</strong> {{ $orangTua->siswa->nama ?? 'N/A' }}
                </div>
                <div class="student-info-item">
                    <strong>Kelas:</strong> {{ $orangTua->siswa->kelas ?? 'N/A' }}
                </div>
            </div>

      @if ($orangTua->siswa)
      <div class="table-responsive">
        <table id="multi-filter-select" class="display table table-striped table-hover">
            <thead>
            <tr>
              <th>Nama Siswa</th>
              <th>Kelas</th>
              <th>NISN</th>
              <th>Jenis Kelamin</th>
              <th>Angkatan</th>
            </tr>
          </thead>
        <tbody>
          <tr>
            <td>{{ $orangTua->siswa->nama }}</td>
            <td>{{ $orangTua->siswa->kelas }}</td>
            <td>{{ $orangTua->siswa->nisn }}</td>
            <td>{{ $orangTua->siswa->jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan' }}</td>
            <td>{{ $orangTua->siswa->angkatan }}</td>
          </tr>
        </tbody>
      </table>
      </div>
      <a href="{{ route('data-orang-tua.index') }}" class="btn btn-secondary btn-sm" title="Kembali">
        <i class="fas fa-arrow-left"></i> 
      </a>
      @else
      <p>Orang tua ini belum terkait dengan data siswa manapun.</p>
      @endif

    </div>
  </div>
</div>
@endsection

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function () {
      var table = $('#multi-filter-select').DataTable({
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 5
      });
    });
  </script>