<head>
    <title>Rekapan Setoran Iuran Kelas</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    {{-- Font Awesome untuk ikon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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

@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.orang_tua')
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Rekapan Pembayaran Kelas {{ $kelasSiswa }} ke Pengurus Paguyuban Besar</h4>
            <div class="d-flex ms-auto">
                <a href="{{ route('orang_tua.pengurus_kelas.form_setoran') }}" class="btn btn-primary btn-sm me-2" title="Tambah Setoran">
                    <i class="fa fa-plus-square" aria-hidden="true"></i>
                </a>
                <a href="{{ route('orang_tua.pengurus_kelas.rekapan_setoran.export') }}" class="btn btn-info btn-sm me-2" title="Export Setoran">
                    <i class="fas fa-download" aria-hidden="true"></i>
                </a>
            </div> 
        </div> 
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="alert alert-info text-center">
                <strong>Kekurangan Pembayaran Kumulatif Saat Ini: 
                    Rp {{ number_format($finalRunningKekurangan, 0, ',', '.') }}
                </strong>
                <br>
                <small>Angka ini menunjukkan total kekurangan pembayaran dari bulan-bulan sebelumnya yang belum tertutupi oleh kelebihan pembayaran.</small>
            </div>

            <div class="table-responsive">
                <table id="multi-filter-select" class="display table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Bulan Iuran</th>
                            <th>Total Pembayaran</th>
                            <th>Jumlah Dibayarkan</th>
                            <th>Tanggal Bayar</th>
                            <th>Status Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($riwayatSetoran as $setoran)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($setoran->created_at)->translatedFormat('F') }}</td>
                                <td>Rp {{ number_format($setoran->total, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($setoran->jumlah, 0, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($setoran->created_at)->translatedFormat('j F Y') }}</td>
                                <td>
                                    @if ($setoran->status_verifikasi)
                                            <span class="badge bg-success">Sudah diverifikasi</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Belum Diverifikasi</span>
                                        @endif
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

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#multi-filter-select').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5,
            columnDefs: [
                { "orderable": false, "targets": [0, 5] }, // Kolom No dan Aksi tidak dapat diurutkan
                { "searchable": false, "targets": [0, 5] } // Kolom No dan Aksi tidak dapat dicari
            ]
        });
    });
</script>
@endpush
