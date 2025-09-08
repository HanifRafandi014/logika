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
        /* Style for the month filter dropdown */
        .month-filter-container {
            margin-bottom: 15px;
            /* Added to align with DataTables default layout */
            display: flex;
            align-items: center;
            justify-content: flex-start; /* Align to start */
            gap: 10px; /* Space between label and select */
        }
        .month-filter-container label {
            margin-right: 0; /* Removed margin-right as gap handles spacing */
            white-space: nowrap; /* Prevent label from wrapping */
        }
        .month-filter-container select {
            padding: 4px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
    </style>
</head>

@extends('layouts.main') {{-- Sesuaikan dengan layout Anda --}}

@section('sidebar')
    @include('layouts.sidebar.orang_tua') {{-- Sesuaikan dengan sidebar pengurus besar --}}
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Riwayat Transaksi Keuangan Pengurus Paguyuban Besar</h4>
            <div class="d-flex ms-auto">
                <a href="{{ route('orang_tua.paguyuban_besar.riwayat_paguyuban_besar.export') }}" class="btn btn-info btn-sm me-2" title="Export Pembayaran Paguyuban Besar">
                    <i class="fas fa-download" aria-hidden="true"></i>
                </a>
            </div> 
        </div>
        <div class="card-body">
            {{-- Filter dropdown added here --}}
            <div class="month-filter-container">
                <label for="monthFilter">Filter Bulan:</label>
                <select id="monthFilter">
                    <option value="">Semua Bulan</option>
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
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="table-responsive">
                <table id="multi-filter-select" class="display table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Tanggal Transaksi</th>
                            <th>Bulan Bayar</th> {{-- Updated column header --}}
                            <th>Bukti Bayar</th> {{-- Added column header --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($riwayatTransaksi as $transaksi)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if ($transaksi->jenis_transaksi == 'pemasukan')
                                        <span class="badge bg-success">Pemasukan</span>
                                    @else
                                        <span class="badge bg-danger">Pengeluaran</span>
                                    @endif
                                </td>
                                <td>{{ $transaksi->kategori ?? '-' }}</td>
                                <td>Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->translatedFormat('d F Y') }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->translatedFormat('F') }}
                                </td>
                                <td>
                                    @if ($transaksi->bukti_transaksi)
                                        <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#buktiModal{{ $transaksi->id }}" title="Lihat Bukti">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <div class="modal fade" id="buktiModal{{ $transaksi->id }}" tabindex="-1" aria-labelledby="buktiModalLabel{{ $transaksi->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="buktiModalLabel{{ $transaksi->id }}">Bukti Transaksi</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <img src="{{ Storage::url($transaksi->bukti_transaksi) }}" class="img-fluid" alt="Bukti Transaksi">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        -
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

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#multi-filter-select').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5
        });

        // Add a custom filter for the month
        $('#monthFilter').on('change', function() {
            var selectedMonth = $(this).val();
            
            // We need to filter based on the 'Bulan bayar' column (index 5)
            // Use a regex search to find the month name anywhere in the cell
            var bulanColumnIndex = 5;

            if (selectedMonth) {
                // Search for the selected month name in the column data
                table.column(bulanColumnIndex).search(selectedMonth, true, false).draw();
            } else {
                // If "Semua Bulan" is selected, clear the search
                table.column(bulanColumnIndex).search('').draw();
            }
        });
    });
</script>
