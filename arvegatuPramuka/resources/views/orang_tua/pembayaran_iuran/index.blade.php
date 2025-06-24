<head>
    <title>Daftar Pembayaran Iuran</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <style>
        .img-thumbnail {
            max-width: 100px;
            height: auto;
        }
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
                <h4 class="card-title mb-0">Pembayaran Iuran Paguyuban</h4>
                <div class="d-flex ms-auto">
                    <a href="{{ route('pembayaran-iuran.create') }}" class="btn btn-primary btn-sm me-2" title="Tambah Pembayaran Iuran">
                        <i class="fa fa-plus-square" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table id="multi-filter-select" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Orang Tua</th>
                                <th>Status</th>
                                <th>Bulan Bayar</th>
                                <th>Jumlah</th>
                                <th>Bukti Bayar</th>
                                <th>Status Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pembayaranSpps as $index => $pembayaranIuran)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $pembayaranIuran->orang_tua->nama ?? 'N/A' }}</td>
                                    <td>{{ $pembayaranIuran->orang_tua->status ?? 'N/A' }}</td>
                                    <td>
                                        @if ($pembayaranIuran->bulan_bayar)
                                            {{ \Carbon\Carbon::parse($pembayaranIuran->bulan_bayar)->translatedFormat('F Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($pembayaranIuran->jumlah, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($pembayaranIuran->bukti_bayar)
                                            <img src="{{ asset('storage/' . $pembayaranIuran->bukti_bayar) }}" alt="Bukti Bayar" class="img-thumbnail">
                                        @else
                                            Tidak ada bukti
                                        @endif
                                    </td>
                                    <td>
                                        @if ($pembayaranIuran->status_pembayaran)
                                            <span class="badge bg-success">Lunas</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Belum Lunas</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('pembayaran-iuran.edit', $pembayaranIuran->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('pembayaran-iuran.destroy', $pembayaranIuran->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pembayaran ini?')" title="Hapus">
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
@endsection

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        // Inisialisasi DataTables
        var table = $('#multi-filter-select').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5,
            // Mengatur kolom yang tidak bisa diurutkan dan dicari
            columnDefs: [
                { "orderable": false, "targets": [0, 5, 7] }, // Kolom No, Bukti Bayar, Aksi tidak dapat diurutkan (index disesuaikan)
                { "searchable": false, "targets": [0, 5, 7] } // Kolom No, Bukti Bayar, Aksi tidak dapat dicari global (index disesuaikan)
            ]
        });

        // Apply the search for each column (input text)
        $('input.column-search').on('keyup change', function () {
            table
                .column($(this).closest('th').index())
                .search(this.value)
                .draw();
        });

        // Apply the search for each column (select dropdown)
        $('select.column-search').on('change', function () {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            table
                .column($(this).closest('th').index())
                .search(val ? '^' + val + '$' : '', true, false)
                .draw();
        });
    });
</script>
