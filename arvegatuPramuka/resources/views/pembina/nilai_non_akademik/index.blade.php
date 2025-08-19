<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous">

    <style>
      /* Styling to match image_070048.png */
      .dataTables_wrapper .dataTables_filter,
      .dataTables_wrapper .dataTables_length {
          display: flex;
          align-items: center;
          margin-bottom: 15px;
      }
      .dataTables_wrapper .dataTables_filter label,
      .dataTables_wrapper .dataTables_length label {
          margin-right: 10px;
      }
      .dataTables_wrapper .dataTables_filter input {
          border-radius: 8px;
          border: 1px solid #ccc;
          padding: 6px 10px;
          font-size: 14px;
      }
      /* Hide the global add button as per new design */
      .card-header .ms-auto {
          display: none;
      }
      /* Custom style for the category filter dropdown to match "Filter Bulan" */
      .filter-group {
          display: flex;
          align-items: center;
          margin-bottom: 15px;
      }
      .filter-group label {
          margin-right: 10px;
          font-weight: normal; /* Match the "Filter Bulan" label font-weight */
      }
      .filter-group select {
          padding: 6px 10px; /* Adjust padding to match the input fields */
          border: 1px solid #ccc;
          border-radius: 8px;
          font-size: 14px;
          width: auto; /* Allow width to adjust based on content */
          min-width: 180px; /* Set a reasonable minimum width */
          max-width: 250px; /* Set a maximum width to prevent it from being too wide */
          height: 34px; /* Match height of other form controls */
      }
      /* *** START OF BUTTON UI IMPROVEMENTS *** */
      .action-buttons {
          display: flex; /* Gunakan flexbox untuk penataan horizontal */
          gap: 4px; /* Jarak antar tombol, bisa disesuaikan */
          justify-content: flex-start; /* Sejajarkan tombol ke kiri */
          align-items: center; /* Sejajarkan tombol secara vertikal di tengah */
          flex-wrap: nowrap; /* Pastikan tombol tidak wrap ke baris baru */
          min-width: 100px; /* Pastikan ada ruang cukup untuk 3 tombol */
      }

      /* Pastikan form di dalam action-buttons juga diatur sebagai flex item */
      .action-buttons form {
          margin: 0; /* Hapus margin default pada form */
          padding: 0; /* Hapus padding default pada form */
          line-height: 1; /* Pastikan line-height tidak memengaruhi tinggi */
          display: flex; /* Jadikan form sebagai flex container untuk tombol di dalamnya */
          align-items: center; /* Pusatkan tombol di dalam form secara vertikal */
          justify-content: center; /* Pusatkan tombol di dalam form secara horizontal */
      }

      .action-buttons .btn {
          padding: 0; /* Hapus padding default agar ukuran lebih presisi */
          font-size: 0.9em; /* Ukuran font ikon */
          border-radius: 5px; /* Sudut membulat */
          display: inline-flex; /* Gunakan flexbox untuk ikon di dalam tombol */
          align-items: center; /* Pusatkan ikon vertikal */
          justify-content: center; /* Pusatkan ikon horizontal */
          height: 30px; /* Tinggi tetap untuk konsistensi */
          width: 30px; /* Lebar tetap untuk konsistensi (membuat tombol persegi) */
          transition: all 0.2s ease-in-out; /* Transisi halus untuk efek hover */
          text-decoration: none; /* Hapus garis bawah pada link */
          box-sizing: border-box; /* Pastikan padding dan border termasuk dalam width/height */
      }

      /* Specific button styles for color */
      .action-buttons .btn-primary { /* Plus button */
          background-color: #007bff; /* Biru Bootstrap */
          border-color: #007bff;
          color: #fff; /* Warna ikon putih */
      }
      .action-buttons .btn-warning { /* Edit button */
          background-color: #ffc107; /* Kuning Bootstrap */
          border-color: #ffc107;
          color: #212529; /* Warna ikon gelap */
      }
      .action-buttons .btn-danger { /* Delete button */
          background-color: #dc3545; /* Merah Bootstrap */
          border-color: #dc3545;
          color: #fff; /* Warna ikon putih */
      }

      /* Hover effects */
      .action-buttons .btn:not([disabled]):hover {
          transform: translateY(-1px); /* Efek sedikit terangkat */
          box-shadow: 0 2px 4px rgba(0,0,0,0.2); /* Bayangan lembut */
          opacity: 0.9; /* Sedikit meredup */
          filter: brightness(1.1); /* Sedikit lebih terang */
      }

      /* Disabled state styling */
      .action-buttons .btn[disabled] {
          opacity: 0.4; /* Redupkan tombol */
          cursor: not-allowed; /* Kursor "tidak diizinkan" */
          box-shadow: none; /* Hapus bayangan */
          transform: none; /* Hapus efek terangkat */
          pointer-events: none; /* Penting: Menonaktifkan semua event pointer, termasuk tooltip */
      }
      /* Remove default button focus outline to avoid double outline with box-shadow */
      .action-buttons .btn:focus {
          outline: none;
          box-shadow: none;
      }
      /* *** END OF BUTTON UI IMPROVEMENTS *** */
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
        <h4 class="card-title mb-0">Data Nilai Non Akademik Siswa</h4>
      </div>
      <div class="card-body">
        {{-- Category Filter Dropdown (Styled to match "Filter Bulan") --}}
        <div class="filter-group">
            <label for="categoryFilter">Filter Kategori:</label>
            <select id="categoryFilter" class="form-control">
                <option value="">Pilih Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}"
                        {{ $selectedCategory == $category ? 'selected' : '' }}
                        @if ($pembinaCategory === 'Pembina Pramuka')
                            {{-- Jika pembina adalah Pembina Pramuka, hanya kategori ini yang aktif --}}
                            @if ($category !== 'Pembina Pramuka') disabled @endif
                        @else
                            {{-- Jika bukan Pembina Pramuka, hanya kategori sesuai pembina yang aktif --}}
                            @if ($pembinaCategory !== $category) disabled @endif
                        @endif
                    >
                        {{ $category }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="table-responsive">
          <table id="multi-filter-select" class="display table table-striped table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>NISN</th>
                <th>Kelas</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
                @foreach ($siswas as $siswa)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $siswa->nama }}</td>
                    <td>{{ $siswa->nisn ?? 'N/A' }}</td>
                    <td>{{ $siswa->kelas ?? 'N/A' }}</td>
                    <td>
                        <div class="action-buttons">
                            @php
                                $hasScore = false;
                                $nilaiNonAkademik = null;
                                if ($selectedCategory) {
                                    $nilaiNonAkademik = $existingScoresMap->get($siswa->id);
                                    $hasScore = ($nilaiNonAkademik !== null);
                                }
                            @endphp

                            {{-- Plus button (Always active if category selected, disabled otherwise) --}}
                            <a href="{{ route('nilai_non_akademik.create', ['kategori' => $selectedCategory]) }}"
                               class="btn btn-sm btn-primary"
                               title="{{ $selectedCategory ? 'Tambah Nilai Non Akademik' : 'Pilih kategori terlebih dahulu untuk menambah nilai' }}"
                               @if (!$selectedCategory) disabled @endif>
                                <i class="fa fa-plus"></i>
                            </a>

                            {{-- Edit button (Active only if category selected AND score exists) --}}
                            <a href="{{ $hasScore ? route('nilai_non_akademik.edit', $nilaiNonAkademik->id) : '#' }}"
                               class="btn btn-sm btn-warning"
                               title="{{ $selectedCategory ? ($hasScore ? 'Edit Nilai' : 'Belum ada nilai untuk kategori ini') : 'Pilih kategori terlebih dahulu untuk mengedit nilai' }}"
                               @if (!$selectedCategory || !$hasScore) disabled @endif>
                                <i class="fas fa-edit"></i>
                            </a>

                            {{-- Delete button (Active only if category selected AND score exists) --}}
                            {{-- REMOVED style="display:inline;" from form tag --}}
                            <form action="{{ $hasScore ? route('nilai_non_akademik.destroy', $nilaiNonAkademik->id) : '#' }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Yakin ingin menghapus nilai ini?')"
                                        title="{{ $selectedCategory ? ($hasScore ? 'Hapus Nilai' : 'Belum ada nilai untuk kategori ini') : 'Pilih kategori terlebih dahulu untuk menghapus nilai' }}"
                                        @if (!$selectedCategory || !$hasScore) disabled @endif>
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
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
        // Initialize DataTables
        var table = $('#multi-filter-select').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5,
        });

        // Ambil kategori pembina & kategori yang dipilih dari Blade
        var pembinaCategory = "{{ $pembinaCategory }}";
        var selectedCategory = "{{ $selectedCategory }}";

        // Jika belum ada kategori di URL, auto-redirect sesuai kategori pembina
        if (!selectedCategory && pembinaCategory) {
            window.location.href = "{{ route('nilai_non_akademik.index') }}?kategori=" + encodeURIComponent(pembinaCategory);
            return; // stop script agar tidak lanjut ke listener dropdown
        }

        // Handle category filter dropdown change
        $('#categoryFilter').on('change', function() {
            var selectedCategory = $(this).val();
            if (selectedCategory) {
                window.location.href = "{{ route('nilai_non_akademik.index') }}?kategori=" + encodeURIComponent(selectedCategory);
            } else {
                window.location.href = "{{ route('nilai_non_akademik.index') }}";
            }
        });
    });
</script>
