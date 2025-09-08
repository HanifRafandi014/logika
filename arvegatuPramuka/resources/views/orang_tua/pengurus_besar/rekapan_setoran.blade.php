@extends('layouts.main') {{-- Layout utama --}}

@section('sidebar')
    @include('layouts.sidebar.orang_tua')
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Rekapan Pembayaran Pengurus Kelas ke Pengurus Pagu Besar</h4>
            <div class="d-flex ms-auto">
                <a id="exportButton" href="#" class="btn btn-info btn-sm me-2" title="Export Pembayaran Setoran">
                    <i class="fas fa-download"></i>
                </a>
            </div>
        </div>

        <div class="card-body">

            {{-- Session Flash Messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Filter Dropdown --}}
            <div class="month-filter-container">
                <label for="monthFilter">Filter Bulan:</label>
                <select id="monthFilter">
                    <option value="">Semua Bulan</option>
                    @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $bulan)
                        <option value="{{ $bulan }}">{{ $bulan }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tabel --}}
            <div class="table-responsive">
                <table id="multi-filter-select" class="display table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pengurus</th>
                            <th>Kelas</th>
                            <th>Tanggal Bayar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $namaPengurusDitampilkan = [];
                        @endphp

                        @foreach ($setoranDariKelas as $setoran)
                            @php
                                $namaPengurus = $setoran->pengurus_kelas->nama ?? 'N/A';
                            @endphp

                            @if (!in_array($namaPengurus, $namaPengurusDitampilkan))
                                @php
                                    $namaPengurusDitampilkan[] = $namaPengurus;
                                @endphp

                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $namaPengurus }}</td>
                                    <td>{{ $setoran->kelas }}</td>
                                    <td>{{ \Carbon\Carbon::parse($setoran->created_at)->translatedFormat('d F Y') }}</td>
                                    <td>
                                        <a href="{{ route('orang_tua.pengurus_besar.verifikasi_pembayaran_pagu', ['pengurus_kelas_nama' => $namaPengurus]) }}" class="btn btn-info btn-sm me-2" title="Verifikasi Setoran">
                                            <i class="fas fa-list"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        .column-search, .month-filter-container select {
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 14px;
        }
        .month-filter-container {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .dataTables_wrapper .dataTables_filter label input {
            border-radius: 8px;
            padding: 6px 10px;
        }
    </style>

    <script>
        $(document).ready(function () {
            const table = $('#multi-filter-select').DataTable({
                orderCellsTop: true,
                fixedHeader: true,
                pageLength: 5,
            });

            $('#monthFilter').on('change', function () {
                const selectedMonth = $(this).val();
                const monthColIndex = 3; // Tanggal Bayar

                if (selectedMonth) {
                    table.column(monthColIndex).search(selectedMonth, true, false).draw();
                } else {
                    table.column(monthColIndex).search('').draw();
                }
            });

            $('#exportButton').on('click', function (e) {
                e.preventDefault();
                const selectedMonth = $('#monthFilter').val();
                let exportUrl = "{{ route('orang_tua.pengurus_besar.rekapan_setoran_kelas.export') }}";
                if (selectedMonth) {
                    exportUrl += '?bulan=' + selectedMonth;
                }
                window.location.href = exportUrl;
            });
        });
    </script>
@endpush
