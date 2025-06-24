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
            <h4 class="card-title">Rekapan Setoran Iuran Kelas {{ $kelasSiswa }} ke Paguyuban Besar</h4>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <a href="{{ route('orang_tua.pengurus_kelas.form_setoran') }}" class="btn btn-sm btn-info mb-3">Buat Setoran Baru</a>
            <div class="table-responsive">
                <table id="multi-filter-select" class="display table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Bulan Iuran Disetor</th>
                            <th>Jumlah Setoran</th>
                            <th>Bukti Setor</th>
                            <th>Status Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($riwayatSetoran as $setoran)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($setoran->bulan_setor)->translatedFormat('F Y') }}</td>
                                <td>Rp {{ number_format($setoran->jumlah, 0, ',', '.') }}</td>
                                <td>
                                    @if ($setoran->bukti_setor)
                                        <a href="{{ Storage::url($setoran->bukti_setor) }}" target="_blank">Lihat Bukti</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($setoran->status_verifikasi)
                                        <span class="badge bg-success">Terverifikasi</span>
                                    @else
                                        <span class="badge bg-danger">Belum Terverifikasi</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada riwayat setoran ke Paguyuban Besar.</td>
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