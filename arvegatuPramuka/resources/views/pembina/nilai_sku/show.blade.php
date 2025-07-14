@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <style>
        /* Sleek Progress Bar Styles */
        .progress-container {
            width: 100%;
            background-color: #f0f0f0;
            border-radius: 5px;
            height: 8px;
            overflow: hidden;
            position: relative;
            margin-bottom: 5px;
        }

        .progress-bar {
            height: 100%;
            background-color: #3490dc;
            border-radius: 5px;
            width: 0%;
            transition: width 0.3s ease-in-out;
            position: absolute;
            left: 0;
            top: 0;
        }

        .progress-text {
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>

<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Detail Penilaian SKU: {{ $siswa->nama }} ({{ ucfirst($tingkatan) }})</h4>
            <a href="{{ route('nilai_sku.student_assessments', ['siswa_id' => $siswa->id]) }}" class="btn btn-secondary btn-sm" title="Kembali ke Daftar Penilaian SKU Siswa">
                <i class="fa fa-arrow-left" aria-hidden="true"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Nama Siswa:</strong> {{ $siswa->nama }}</p>
                    <p><strong>Tingkatan:</strong> {{ ucfirst($tingkatan) }}</p>
                    <p><strong>Tanggal Penilaian:</strong> {{ \Carbon\Carbon::parse($assessmentDate)->translatedFormat('d F Y') }}</p>
                </div>
                <div class="col-md-6">
                    @if ($buktiPdf)
                        <p><strong>Bukti PDF:</strong> 
                            <a href="{{ asset($buktiPdf) }}" target="_blank" class="btn btn-sm btn-primary">Lihat PDF</a>
                        </p>
                    @else
                        <p><strong>Bukti PDF:</strong> Tidak ada bukti PDF diunggah.</p>
                    @endif
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Progress SKU:</h5>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: {{ $progressPercentage }}%;"></div>
                    </div>
                    <div class="progress-text">{{ $checkedCount }}/{{ $totalPossibleItems }} ({{ round($progressPercentage) }}%)</div>
                </div>
                <div class="col-md-6">
                    <h5>Status SKU:</h5>
                    @if ($overallStatus)
                        <span class="badge bg-success">Lulus</span>
                    @else
                        <span class="badge bg-warning text-dark">Belum Lulus</span>
                    @endif
                </div>
            </div>

            <hr>

            <h5>Detail SKU Items:</h5>
            @if ($allSkuItems->isEmpty())
                <p>Tidak ada item SKU yang ditemukan untuk tingkatan ini.</p>
            @else
                <div class="table-responsive">
                    <table id="sku-items-table" class="display table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Deskripsi SKU</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allSkuItems as $skuItem)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $skuItem->deskripsi_sku }}</td>
                                    <td>
                                        @if (isset($assessedSkuMap[$skuItem->id]) && $assessedSkuMap[$skuItem->id]->status)
                                            <span class="badge bg-success">Tercapai</span>
                                        @else
                                            <span class="badge bg-danger">Belum Tercapai</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#sku-items-table').DataTable({
            pageLength: 10 // Set default pagination length
        });
    });
</script>
@endsection