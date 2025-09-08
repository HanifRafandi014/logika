kode orang_tua.pembayaran_iuran.index :
@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.orang_tua')
@endsection

@section('content')
<style>
    .card-body {
        padding: 10px;
    }

    .card-header {
        padding: 10px 15px;
    }

    .info-box-row {
        margin-bottom: 10px !important;
    }

    .info-box {
        padding: 10px !important;
    }

    .info-box h6 {
        font-size: 0.8rem;
        margin-bottom: 3px !important;
    }

    .info-box h5 {
        font-size: 1.1rem;
        margin-bottom: 0 !important;
    }

    .card-body > p {
        margin-top: 10px;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }

    .table.table-bordered th,
    .table.table-bordered td {
        padding: 2px 6px;
        vertical-align: middle;
        font-size: 0.75rem;
        height: 26px;
    }

    .form-check {
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .form-check-input {
        margin-top: 0;
        margin-left: 0;
    }

    .form-check-label {
        margin-bottom: 0;
        font-size: 0.7rem;
    }

    .badge {
        font-size: 0.65rem;
        padding: 0.2em 0.4em;
    }

    .btn-sm.btn-info {
        font-size: 0.65rem;
        padding: 2px 6px;
        line-height: 1;
    }

    .modal-title {
        font-size: 0.85rem;
    }

    .modal-body img {
        max-height: 300px;
    }

    @media (max-width: 768px) {
        .info-box-row .col-md-3 {
            flex: 0 0 50%;
            max-width: 50%;
            margin-bottom: 10px;
        }
    }
</style>


<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Pembayaran Paguyuban Orang Tua</h4>
        </div>
        <div class="card-body">
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
            <div class="row g-3 mb-4 info-box-row">
                <div class="col-md-3 col-6">
                    <div class="info-box shadow-sm rounded p-3 bg-light border-start border-4 border-primary">
                        <h6 class="text-muted mb-1">Nama Orang Tua</h6>
                        <h5 class="mb-0 text-primary">{{ Auth::user()->orang_tua->nama ?? 'N/A' }}</h5>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="info-box shadow-sm rounded p-3 bg-light border-start border-4 border-success">
                        <h6 class="text-muted mb-1">Nama Siswa</h6>
                        <h5 class="mb-0 text-success">{{ Auth::user()->orang_tua->siswa->nama ?? 'N/A' }}</h5>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="info-box shadow-sm rounded p-3 bg-light border-start border-4 border-warning">
                        <h6 class="text-muted mb-1">Kelas Siswa</h6>
                        <h5 class="mb-0 text-warning">{{ Auth::user()->orang_tua->siswa->kelas ?? 'N/A' }}</h5>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="info-box shadow-sm rounded p-3 bg-light border-start border-4 border-danger">
                        <h6 class="text-muted mb-1">Total Biaya Iuran</h6>
                        <h5 class="mb-0 text-danger" id="total-biaya">
                             Rp. 0
                        </h5>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box shadow-sm rounded p-3 bg-light border-start border-4 border-secondary">
                        <h6 class="text-muted mb-1">Total yang Sudah Dibayarkan</h6>
                        <h5 class="mb-0 text-secondary" id="total-biaya-dibayar">
                            Rp. {{ number_format($totalSudahDibayar, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>

            <p><b>*Biaya per bulan = Rp. {{ number_format($besaranBiaya->total_biaya ?? 0, 0, ',', '.') }} * jumlah bulan yang dibayarkan</b></p>

            <form action="{{ route('pembayaran-iuran.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="orang_tua_id" value="{{ Auth::user()->orang_tua->id ?? '' }}">
                <input type="hidden" name="siswa_id" value="{{ Auth::user()->orang_tua->siswa_id ?? '' }}">

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Tahun</th> {{-- Kolom Tahun --}}
                                <th>Status Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bulanDalamTahun as $bulanAngka => $bulanNama)
                                <tr>
                                    <td>{{ $bulanNama }}</td>
                                    <td>
                                        @if (isset($statusBulanan[$bulanAngka]))
                                            {{-- Jika sudah ada pembayaran, tampilkan tahun dari statusBulanan --}}
                                            {{ $statusBulanan[$bulanAngka]['tahun'] ?? $tahunSekarang }}
                                        @else
                                            {{-- Jika belum ada pembayaran, tampilkan tahun saat ini --}}
                                            {{ $tahunSekarang }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($statusBulanan[$bulanAngka]))
                                            @if ($statusBulanan[$bulanAngka]['status'] == 1)
                                                <span class="badge bg-success">Sudah Diverifikasi</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Belum Diverifikasi</span>
                                            @endif
                                        @else
                                            <span class="badge bg-danger">Belum Bayar</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if (!isset($statusBulanan[$bulanAngka]))
                                            {{-- Perbaiki styling di sini untuk checkbox --}}
                                            <div class="form-check">
                                                <input class="form-check-input bulan-checkbox" type="checkbox" name="bulan_bayar[]" value="{{ $bulanAngka }}" id="bulanBayar{{ $bulanAngka }}">
                                                <label class="form-check-label" for="bulanBayar{{ $bulanAngka }}">
                                                    Pilih Bulan
                                                </label>
                                            </div>
                                        @else
                                            @if ($statusBulanan[$bulanAngka]['status'] == 1)
                                                <span>-</span>
                                            @else
                                                <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#buktiBayarModal{{ $bulanAngka }}">Lihat Bukti</a>
                                                <div class="modal fade" id="buktiBayarModal{{ $bulanAngka }}" tabindex="-1" aria-labelledby="buktiBayarModalLabel{{ $bulanAngka }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="buktiBayarModalLabel{{ $bulanAngka }}">Bukti Bayar {{ $bulanNama }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                @if ($statusBulanan[$bulanAngka]['bukti_bayar'])
                                                                    <img src="{{ Storage::url($statusBulanan[$bulanAngka]['bukti_bayar']) }}" class="img-fluid" alt="Bukti Pembayaran">
                                                                @else
                                                                    <p>Tidak ada bukti bayar tersedia.</p>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mb-3 mt-4">
                    <label for="bukti_bayar" class="form-label">Unggah Bukti Bayar (Opsional)</label>
                    <input type="file" class="form-control @error('bukti_bayar') is-invalid @enderror" id="bukti_bayar" name="bukti_bayar" accept="image/*">
                    <small class="form-text text-muted">Format yang diizinkan: JPEG, PNG, JPG. Ukuran maksimal: 2MB.</small>
                    @error('bukti_bayar')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary me-2">Submit Pembayaran</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const checkboxes = document.querySelectorAll('.bulan-checkbox');
        const totalBiayaEl = document.getElementById('total-biaya');
        const biayaPerBulan = {{ $besaranBiaya->total_biaya ?? 0 }};

        function updateTotal() {
            let total = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    total += biayaPerBulan;
                }
            });

            // Format ke Rupiah (ID)
            totalBiayaEl.innerText = 'Rp. ' + total.toLocaleString('id-ID');
        }

        // Jalankan saat pertama kali
        updateTotal();

        // Event ketika dicentang/diubah
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateTotal);
        });
    });
</script>
@endpush