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

        .month-filter-container {
            margin-bottom: 15px;
            display: none; /* Sembunyikan filter bulan karena kolom bulan sudah tidak ditampilkan per baris */
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
        }

        .month-filter-container select {
            padding: 4px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            display: inline-block;
            vertical-align: middle;
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
        }

        .dataTables_wrapper .dataTables_filter label input {
            border-radius: 8px;
            border: 1px solid #ccc;
            padding: 6px 10px;
            font-size: 14px;
        }

        .dataTables_wrapper:after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>

@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.orang_tua')
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Daftar Anggota Paguyuban Kelas {{ $kelasSiswa }}</h4> {{-- Ubah judul --}}
            <div class="d-flex ms-auto">
                <a id="exportButton" href="{{ route('orang_tua.data-pembayaran-kelas.export') }}" class="btn btn-info btn-sm me-2" title="Export Pembayaran">
                    <i class="fas fa-download"></i>
                </a>
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
            </div>
        </div>
        <div class="card-body">
            {{-- Sembunyikan filter bulan, karena kita tidak menampilkan bulan bayar per baris lagi --}}
            <div class="month-filter-container" style="display: none;">
                <label for="monthFilter">Filter Bulan:</label>
                <select id="monthFilter">
                    <option value="">Semua Bulan</option>
                    {{-- Opsi bulan tetap ada jika Anda ingin mengaktifkan kembali filter di JS untuk DataTables,
                         meskipun kolom bulan tidak ditampilkan. Atau bisa dihapus sepenuhnya jika tidak ada niat filter bulan. --}}
                    <option value="Januari">Januari</option>
                    <option value="Februari">Februari</option>
                    <option value="Maret">Maret</option>
                    <option value="April">April</option>
                    <option value="Mei">Mei</option>
                    <option value="Juni">Juni</option>
                    <option value="Juli">Juli</option>
                    <option value="Agustus">Agustus</option>
                    <option value="September">September</option>
                    <option value="Oktober">Oktober</option>
                    <option value="November">November</option>
                    <option value="Desember">Desember</option>
                </select>
            </div>
            <div class="table-responsive">
                <table id="multi-filter-select" class="display table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Nama Orang Tua</th>
                            {{-- Kolom Bulan Bayar dihapus --}}
                            <th>Tanggal Pembayaran</th> {{-- Ubah judul kolom --}}
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Loop pada koleksi pembayaran unik --}}
                        @forelse ($riwayatPembayaransUnik as $pembayaran) 
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $pembayaran->siswa->nama ?? 'N/A' }}</td>
                                <td>{{ $pembayaran->orang_tua->nama ?? 'N/A' }}</td>
                                {{-- Menampilkan tanggal created_at dari transaksi pembayaran terakhir --}}
                                <td>{{ \Carbon\Carbon::parse($pembayaran->created_at)->translatedFormat('j F Y') }}</td>
                                <td>
                                    {{-- Aksi untuk melihat detail atau verifikasi (mungkin mengarahkan ke halaman verifikasi spesifik untuk siswa/ortu ini) --}}
                                    <a href="{{ route('orang_tua.pengurus_kelas.verifikasi_pembayaran_iuran', [
                                        'orang_tua_id' => $pembayaran->orang_tua_id,
                                        'siswa_id' => $pembayaran->siswa_id,
                                        // Anda mungkin ingin menambahkan parameter lain jika halaman verifikasi perlu detail lebih lanjut
                                    ]) }}" class="btn btn-info btn-sm me-2" title="Lihat Detail Pembayaran">
                                        <i class="fas fa-list"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data pembayaran untuk kelas ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        var table = $('#multi-filter-select').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5,
            dom: '<"top"lf>rt<"bottom"ip><"clear">',
            // Matikan sorting atau pencarian untuk kolom yang dihapus
            "columnDefs": [
                { "orderable": false, "targets": [4] }, // Kolom Aksi tidak bisa disortir
                { "searchable": false, "targets": [0, 4] } // Kolom No dan Aksi tidak bisa dicari
            ]
        });

        var monthFilterDropdown = $('#monthFilter');
        var exportButton = $('#exportButton');
        var originalExportUrl = exportButton.attr('href');

        function updateExportButtonUrl() {
            var selectedMonth = monthFilterDropdown.val();
            var newUrl = originalExportUrl;

            if (selectedMonth) {
                 newUrl += '?monthFilter=' + encodeURIComponent(selectedMonth);
            }
            exportButton.attr('href', newUrl);
        }

        updateExportButtonUrl();
    });
</script>
@endpush