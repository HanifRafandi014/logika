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
    @include('layouts.sidebar.orang_tua')
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Daftar Pembayaran Iuran Per Siswa Kelas {{ $kelasSiswa }}</h4>
            <div class="d-flex ms-auto">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
            </div>
        </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="multi-filter-select" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Nama Orang Tua</th>
                                <th>Bulan Iuran</th>
                                <th>Jumlah</th>
                                <th>Tanggal Bayar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dataRekapan as $rekapan)
                                @if ($rekapan['riwayat_pembayaran']->isEmpty())
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $rekapan['siswa_nama'] }}</td>
                                        <td>{{ $rekapan['orang_tua_nama'] }}</td>
                                        <td colspan="4" class="text-center">Belum ada pembayaran</td>
                                    </tr>
                                @else
                                    @foreach ($rekapan['riwayat_pembayaran'] as $pembayaran)
                                        <tr>
                                            <td>{{ $loop->parent->iteration }}</td>
                                            <td>{{ $rekapan['siswa_nama'] }}</td>
                                            <td>{{ $rekapan['orang_tua_nama'] }}</td>
                                            <td>{{ \Carbon\Carbon::parse($pembayaran->bulan_bayar)->translatedFormat('F Y') }}</td>
                                            <td>Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($pembayaran->bulan_bayar)->translatedFormat('j F Y') }}</td>
                                            <td>
                                                @if ($pembayaran->status_pembayaran)
                                                    <span class="badge bg-success">Lunas</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Belum Lunas</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data pembayaran untuk kelas ini.</td>
                                </tr>
                            @endforelse
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
      var table = $('#multi-filter-select').DataTable({ // Make sure this ID matches your table if you want DataTables on it.
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 5
      });
    });
</script>