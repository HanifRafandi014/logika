@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>

<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Detail SKK: {{ $siswa->nama }} ({{ ucfirst($tingkatan) }} - {{ $jenis_skk }})</h4>
            {{-- Link back to the student's grouped assessments --}}
            <a href="{{ route('nilai_skk.student_assessments', ['siswa_id' => $siswa->id]) }}" class="btn btn-secondary btn-sm" title="Kembali ke Daftar Penilaian Siswa">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Nama Siswa:</strong> {{ $siswa->nama }}</p>
                    <p><strong>Tingkatan:</strong> {{ ucfirst($tingkatan) }}</p>
                    <p><strong>Jenis SKK:</strong> {{ $jenis_skk }}</p>
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

            {{-- Optional: If you want to show overall progress/status for this specific SKK group here too --}}
            {{-- (though it's already on student_assessments, it might be redundant here) --}}
            {{-- <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Progress SKK:</h5>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: {{ $progressPercentage }}%;"></div>
                    </div>
                    <div class="progress-text">{{ $checkedCount }}/{{ $totalPossibleItems }} ({{ round($progressPercentage) }}%)</div>
                </div>
                <div class="col-md-6">
                    <h5>Status SKK:</h5>
                    @if ($overallStatus)
                        <span class="badge bg-success">Lulus</span>
                    @else
                        <span class="badge bg-warning text-dark">Belum Lulus</span>
                    @endif
                </div>
            </div>
            <hr> --}}

            <h5>Detail SKK Items:</h5>
            @if ($allSkkItems->isEmpty())
                <p>Tidak ada item SKK yang ditemukan untuk tingkatan dan jenis SKK ini.</p>
            @else
                <div class="table-responsive">
                    <table id="skk-items-table" class="display table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Deskripsi SKK</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allSkkItems as $skkItem)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $skkItem->keterangan_skk }}</td>
                                    <td>
                                        @if (isset($assessedSkkMap[$skkItem->id]) && $assessedSkkMap[$skkItem->id]->status)
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
        $('#skk-items-table').DataTable({
            pageLength: 5 // Set default pagination length
        });
    });
</script>
@endsection