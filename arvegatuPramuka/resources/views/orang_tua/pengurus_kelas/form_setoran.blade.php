{{-- resources/views/orang_tua/pengurus_kelas/form_setoran.blade.php --}}
@extends('layouts.main') {{-- Sesuaikan dengan layout Anda --}}

@section('sidebar')
    @include('layouts.sidebar.orang_tua') {{-- Sesuaikan dengan sidebar pengurus kelas --}}
@endsection

@section('content')
<style>
    .card-body {
        padding: 15px; /* Mengurangi padding dari 20px */
    }

    /* Mengurangi margin pada header card */
    .card-header {
        padding: 10px 15px; /* Mengurangi padding */
    }

    /* Mengurangi margin pada row info boxes */
    .info-box-row {
        margin-bottom: 10px !important; /* Mengurangi margin-bottom dari mb-4 */
    }

    /* Mengurangi padding pada info boxes dan ukuran font */
    .info-box {
        padding: 10px !important; /* Mengurangi padding dari p-3 */
    }
    .info-box h6 {
        font-size: 0.8rem; /* Ukuran font lebih kecil */
        margin-bottom: 3px !important; /* Mengurangi margin-bottom */
    }
    .info-box h5 {
        font-size: 1.1rem; /* Ukuran font lebih kecil */
        margin-bottom: 0 !important;
    }
    
    /* Mengurangi margin pada paragraf di bawah info box */
    .card-body > p {
        margin-top: 10px;
        margin-bottom: 10px; /* Mengurangi jarak di bawah paragraf */
        font-size: 0.9rem; /* Ukuran font lebih kecil */
    }

    .table.table-bordered th,
    .table.table-bordered td {
        padding: 2px 8px; /* Padding vertikal 2px, horizontal 8px */
        vertical-align: middle; /* Rata tengah vertikal */
        font-size: 0.9rem; /* Ukuran font lebih kecil */
        height: 30px; /* Menambahkan fixed height untuk konsistensi */
    }
    
    /* Mengurangi margin pada form check di dalam tabel */
    .form-check {
        margin: 0; /* Hapus semua margin */
        padding: 0; /* Hapus semua padding */
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%; /* Pastikan checkbox berada di tengah vertikal */
    }
    .form-check-input {
        margin-top: 0;
        margin-left: 0;
    }
    .form-check-label {
        margin-bottom: 0;
    }

    /* Mengurangi padding pada badge status */
    .badge {
        font-size: 0.75rem; /* Sedikit lebih kecil dari font tabel */
        padding: 0.25em 0.5em; /* Padding badge lebih kecil */
    }

    /* Mengurangi margin pada input form dan tombol submit */
    .mb-3.mt-4 {
        margin-top: 15px !important;
        margin-bottom: 15px !important;
    }
    .form-label, .form-text {
        font-size: 0.9rem;
    }

    /* Mengurangi margin pada tombol submit */
    .d-flex.justify-content-end.mt-4 {
        margin-top: 15px !important;
    }
    
    /* Responsive adjustments */
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
            <h4 class="card-title mb-0">Form Pembayaran Paguyuban ke Pengurus Paguyuban Besar</h4>
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
            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row g-3 mb-4 info-box-row">
                <div class="col-md-3 col-6">
                    <div class="info-box shadow-sm rounded p-3 bg-light border-start border-4 border-primary">
                        <h6 class="text-muted mb-1">Nama Pengurus Kelas</h6>
                        <h5 class="mb-0 text-primary">{{ Auth::user()->orang_tua->nama ?? 'N/A' }}</h5>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="info-box shadow-sm rounded p-3 bg-light border-start border-4 border-success">
                        <h6 class="text-muted mb-1">Jumlah Siswa Kelas</h6>
                        <h5 class="mb-0 text-success" id="jumlah_siswa_display">{{ $jumlahSiswaKelas ?? 'N/A' }}</h5>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="info-box shadow-sm rounded p-3 bg-light border-start border-4 border-warning">
                        <h6 class="text-muted mb-1">Kelas</h6>
                        <h5 class="mb-0 text-warning">{{ $kelasSiswa ?? 'N/A' }}</h5>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="info-box shadow-sm rounded p-3 bg-light border-start border-4 border-danger">
                        <h6 class="text-muted mb-1">Total Paguyuban</h6>
                        <h5 class="mb-0 text-danger" id="total_setoran_display">Rp. 0</h5>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box shadow-sm rounded p-3 bg-light border-start border-4 border-secondary">
                        <h6 class="text-muted mb-1">Total yang Sudah Dibayarkan</h6>
                        <h5 class="mb-0 text-secondary" id="total-biaya-dibayar">
                            Rp. 0
                        </h5>
                    </div>
                </div>
            </div>
            
            <p><b>*Biaya = Rp. {{ number_format($besaranBiaya->nominal_pagu_besar ?? 0, 0, ',', '.') }} * Jumlah Siswa * Jumlah Bulan yang Dibayarkan</b></p>

            {{-- Form Setoran --}}
            <form action="{{ route('orang_tua.pengurus_kelas.proses_setoran') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                {{-- Hidden input for Besaran Biaya ID --}}
                <input type="hidden" name="besaran_biaya_id" value="{{ $besaranBiaya->id ?? '' }}">
                <input type="hidden" id="nominal_per_siswa" value="{{ $besaranBiaya->nominal_pagu_besar ?? 0 }}">
                <input type="hidden" name="jumlah_siswa" id="jumlah_siswa_hidden"> {{-- Hidden input for JS calculation --}}

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Daftar bulan dalam setahun
                                $months = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            @endphp

                            @foreach ($months as $monthNumber => $monthName)
                                @php
                                    $monthFullName = $monthName;
                                    $setoranStatus = 'Belum Bayar';
                                    $setoranObjForMonth = null;

                                    // Check status from pre-processed map
                                    if (isset($setoranStatusesByMonth[$monthNumber])) {
                                        $statusData = $setoranStatusesByMonth[$monthNumber];
                                        $setoranStatus = $statusData['status'] == 1 ? 'Sudah Diverifikasi' : 'Belum Diverifikasi';
                                        $setoranObjForMonth = $statusData['setoran_obj'];
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $monthFullName }}</td>
                                    <td>
                                        @if ($setoranStatus == 'Sudah Diverifikasi')
                                            <span class="badge bg-success">{{ $setoranStatus }}</span>
                                        @elseif ($setoranStatus == 'Belum Diverifikasi')
                                            <span class="badge bg-warning text-dark">{{ $setoranStatus }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ $setoranStatus }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($setoranObjForMonth)
                                            @if ($setoranStatus == 'Belum Diverifikasi')
                                                <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#buktiSetorModal{{ $setoranObjForMonth->id }}">
                                                    Lihat Bukti
                                                </a>
                                                <div class="modal fade" id="buktiSetorModal{{ $setoranObjForMonth->id }}" tabindex="-1" aria-labelledby="buktiSetorModalLabel{{ $setoranObjForMonth->id }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="buktiSetorModalLabel{{ $setoranObjForMonth->id }}">Bukti Pembayaran {{ $monthFullName }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                @if ($setoranObjForMonth->bukti_setor)
                                                                    <img src="{{ Storage::url($setoranObjForMonth->bukti_setor) }}" class="img-fluid" alt="Bukti Setoran">
                                                                @else
                                                                    <p>Tidak ada bukti pembayaran tersedia.</p>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span>-</span> {{-- Already verified, no action needed here --}}
                                            @endif
                                        @else
                                            <div class="form-check">
                                                <input class="form-check-input month-checkbox" type="checkbox" name="bulan_setor[]" value="{{ $monthNumber }}" id="bulanSetor{{ $monthNumber }}">
                                                <label class="form-check-label" for="bulanSetor{{ $monthNumber }}">
                                                    Pilih Bulan
                                                </label>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Input for total actual amount and proof of payment --}}
                <div class="mb-3 mt-4">
                    <label for="jumlah_yang_disetorkan" class="form-label">Jumlah Uang yang Dibayarkan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp.</span>
                        <input type="number" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah_yang_disetorkan" name="jumlah" value="{{ old('jumlah') }}" min="0" required>
                        @error('jumlah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="form-text text-muted">Masukkan jumlah uang yang Anda bayarkan tanpa titik. (Contoh: 1500000)</small>
                </div>

                <div class="mb-3 mt-4">
                    <label for="bukti_setor" class="form-label">Upload Bukti Pembayaran</label>
                    <input type="file" class="form-control @error('bukti_setor') is-invalid @enderror" id="bukti_setor" name="bukti_setor" accept="image/*">
                    @error('bukti_setor')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Unggah foto bukti transfer/pembayaran (maks. 2MB, format: jpg, png, jpeg).</small>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary me-2">Proses Pembayaran</button>
                    <a href="{{ route('orang_tua.pengurus_kelas.rekapan_setoran') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const monthCheckboxes = document.querySelectorAll('.month-checkbox');
        const nominalPerSiswa = parseFloat(document.getElementById('nominal_per_siswa').value);
        const totalSetoranDisplay = document.getElementById('total_setoran_display');
        const jumlahSiswaDisplay = document.getElementById('jumlah_siswa_display');
        const jumlahSiswaHiddenInput = document.getElementById('jumlah_siswa_hidden');

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        }

        function updateJumlahSetoran() {
            let totalCheckedMonths = 0;
            monthCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    totalCheckedMonths++;
                }
            });

            // Get the total number of students in the class from the display element
            const totalSiswaInClass = parseInt(jumlahSiswaDisplay.textContent.trim()) || 0;
            
            // Calculate the total setoran based on the number of checked months and students
            const totalSetoranDihitung = totalCheckedMonths * totalSiswaInClass * nominalPerSiswa;

            // Update the display and hidden input
            totalSetoranDisplay.textContent = formatRupiah(totalSetoranDihitung);
            jumlahSiswaHiddenInput.value = totalSiswaInClass; // Pass the total students to the controller
        }

        // Add event listeners to all checkboxes
        monthCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateJumlahSetoran);
        });

        // Initial update on page load
        updateJumlahSetoran();
    });
</script>
@endpush
@endsection