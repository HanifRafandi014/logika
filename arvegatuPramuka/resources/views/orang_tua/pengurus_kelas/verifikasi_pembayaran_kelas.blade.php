{{-- resources/views/orang_tua/pengurus_kelas/verifikasi_pembayaran_kelas.blade.php --}}
@extends('layouts.main') {{-- Adjust to your main layout --}}
@section('sidebar')
    @include('layouts.sidebar.orang_tua') {{-- Adjust to your parent sidebar --}}
@endsection

@section('content')
<style>
    /* General container and card styling */
    .container {
        padding-top: 20px;
    }
    .card {
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-top: 20px; /* Space from the header */
    }
    .card-body {
        padding: 20px;
    }

    /* Header section (Rekapan Pembayaran Orang Tua) */
    .header-section {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    .back-arrow {
        font-size: 1.5em; /* Larger arrow */
        color: #333;
        margin-right: 15px;
        text-decoration: none;
    }
    .page-title {
        margin-bottom: 0;
        font-size: 1.5em;
        font-weight: bold;
        color: #333;
    }

    /* Styling for each payment item in the list, mimicking the image structure */
    .payment-item-card {
        background-color: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 15px;
        padding: 15px;
        display: flex;
        flex-direction: column; /* Stack details vertically */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .payment-row-detail {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 0.95em;
    }
    .payment-row-detail:last-of-type {
        margin-bottom: 0;
    }

    .detail-label {
        font-weight: bold;
        color: #666;
        width: 120px; /* Fixed width for labels */
        flex-shrink: 0;
    }
    .detail-value {
        color: #333;
        flex-grow: 1;
        text-align: right;
    }
    .detail-value.left-align { /* For status and action to be left aligned */
        text-align: left;
    }

    /* Specific styling for elements inside payment item */
    .payment-amount-text {
        font-size: 1.1em;
        color: #007bff; /* Blue color for amount */
        font-weight: bold;
    }
    .payment-status-badge {
        font-size: 0.8em;
        padding: 0.3em 0.6em;
        border-radius: 0.25rem;
        display: inline-block; /* Ensure it respects padding */
    }
    .status-unverified {
        background-color: #ffc107; /* Orange/Yellow for unverified */
        color: #212529;
    }
    .status-verified {
        background-color: #28a745; /* Green for verified */
        color: #fff;
    }
    .action-buttons {
        display: flex;
        gap: 10px;
        justify-content: flex-end; /* Align buttons to the right */
    }
    .btn-action-custom {
        padding: 0.4em 0.8em;
        font-size: 0.8em;
        border-radius: 5px;
    }
    .btn-action-custom.verify {
        background-color: #28a745; /* Green */
        color: #fff;
        border: none;
    }
    .btn-action-custom.bukti {
        background-color: #17a2b8; /* Info blue */
        color: #fff;
        border: none;
    }

    /* Modal styling for bukti bayar */
    .modal-body img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }
    .modal-content {
        border-radius: 10px;
    }
    .modal-header {
        border-bottom: none;
    }
    .modal-footer {
        border-top: none;
    }

    /* Custom confirmation modal styles */
    .custom-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1050; /* Above Bootstrap modals */
    }

    .custom-modal-content {
        background-color: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        max-width: 400px;
        width: 90%;
        text-align: center;
    }

    .custom-modal-header {
        font-size: 1.2em;
        font-weight: bold;
        margin-bottom: 15px;
        color: #333;
    }

    .custom-modal-body {
        margin-bottom: 20px;
        color: #555;
    }

    .custom-modal-footer button {
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }

    .custom-modal-footer .btn-cancel {
        background-color: #6c757d; /* Secondary gray */
        color: #fff;
        margin-right: 10px;
    }

    .custom-modal-footer .btn-confirm {
        background-color: #28a745; /* Green */
        color: #fff;
    }
</style>

<div class="col-md-12">
    <div class="header-section">
        <a href="{{ url()->previous() }}" class="back-arrow">
            <i class="fas fa-arrow-left"></i> {{-- Font Awesome arrow icon --}}
        </a>
        {{-- Menggunakan $kelasPengurus yang dikirim dari controller --}}
        <h4 class="page-title">Verifikasi Pembayaran Orang Tua Kelas {{ $kelasPengurus ?? 'N/A' }}</h4>
    </div>

    <div class="card">
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

            {{-- Menggunakan $loggedInUser yang dikirim dari controller untuk detail pengurus --}}
            <p class="mb-3">Pengurus yang login: <strong>{{ $loggedInUser->orang_tua->nama ?? 'N/A' }}</strong></p>

            @forelse ($pembayaranUntukVerifikasi as $index => $pembayaran)
                <div class="payment-item-card">
                    <div class="payment-row-detail">
                        <span class="detail-label">Siswa:</span>
                        <span class="detail-value">{{ $pembayaran->siswa->nama ?? 'N/A' }}</span>
                    </div>
                    <div class="payment-row-detail">
                        <span class="detail-label">Orang Tua:</span>
                        <span class="detail-value">{{ $pembayaran->orang_tua->nama ?? 'N/A' }}</span>
                    </div>
                    <div class="payment-row-detail">
                        <span class="detail-label">Bulan Iuran:</span>
                        <span class="detail-value">
                            @if ($pembayaran->bulan_bayar)
                                @php
                                    $decodedMonths = json_decode($pembayaran->bulan_bayar, true);
                                    $bulanNamaArray = [];
                                    if (is_array($decodedMonths)) {
                                        foreach ($decodedMonths as $bulanAngka) {
                                            // Mengambil nama bulan dari array $bulanDalamTahun yang dikirim dari controller
                                            $bulanNamaArray[] = $bulanDalamTahun[$bulanAngka] ?? 'Tidak Dikenal';
                                        }
                                    }
                                @endphp
                                @if(is_array($bulanNamaArray) && !empty($bulanNamaArray))
                                    {{ implode(', ', $bulanNamaArray) }}
                                @else
                                    N/A
                                @endif
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="payment-row-detail">
                        <span class="detail-label">Jumlah Bayar:</span>
                        <span class="detail-value payment-amount-text">
                            Rp {{ number_format($pembayaran->total_bayar_final, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="payment-row-detail">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value detail-value-status left-align">
                            @if ($pembayaran->status_pembayaran == 0)
                                <span class="payment-status-badge status-unverified">Belum Diverifikasi</span>
                            @else
                                <span class="payment-status-badge status-verified">Sudah Diverifikasi</span>
                            @endif
                        </span>
                    </div>
                    <div class="payment-row-detail">
                        <span class="detail-label">Aksi:</span>
                        <span class="detail-value action-buttons left-align">
                            @if ($pembayaran->bukti_bayar)
                                <a href="#" class="btn btn-action-custom bukti" data-bs-toggle="modal" data-bs-target="#buktiBayarModal{{ $pembayaran->id }}">
                                    Lihat Bukti
                                </a>
                                <div class="modal fade" id="buktiBayarModal{{ $pembayaran->id }}" tabindex="-1" aria-labelledby="buktiBayarModalLabel{{ $pembayaran->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="buktiBayarModalLabel{{ $pembayaran->id }}">Bukti Pembayaran</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <img src="{{ Storage::url($pembayaran->bukti_bayar) }}" alt="Bukti Pembayaran" class="img-fluid">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($pembayaran->status_pembayaran == 0)
                                <form action="{{ route('pembayaran-iuran.verify', $pembayaran->id) }}" method="POST" class="verify-form">
                                    @csrf
                                    <button type="button" class="btn btn-action-custom verify verify-button" data-payment-id="{{ $pembayaran->id }}">Verifikasi</button>
                                </form>
                            @else
                                <span class="btn btn-action-custom btn-secondary" disabled>Sudah</span>
                            @endif
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-center py-4">Tidak ada pembayaran yang perlu diverifikasi di kelas ini.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="custom-modal-backdrop" id="customConfirmModal" style="display: none;">
    <div class="custom-modal-content">
        <div class="custom-modal-header">Konfirmasi Verifikasi</div>
        <div class="custom-modal-body">Apakah Anda yakin ingin memverifikasi pembayaran ini?</div>
        <div class="custom-modal-footer">
            <button class="btn-cancel" onclick="hideCustomConfirmModal()">Batal</button>
            <button class="btn-confirm" id="confirmVerificationButton">Verifikasi</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let currentVerifyForm = null; // Variable to store the form to be submitted

    document.addEventListener('DOMContentLoaded', function() {
        // Attach click listener to all "Verifikasi" buttons
        document.querySelectorAll('.verify-button').forEach(button => {
            button.addEventListener('click', function() {
                currentVerifyForm = this.closest('.verify-form'); // Get the parent form
                showCustomConfirmModal(); // Show the custom confirmation modal
            });
        });

        // Attach click listener to the confirm button in the custom modal
        document.getElementById('confirmVerificationButton').addEventListener('click', function() {
            if (currentVerifyForm) {
                currentVerifyForm.submit(); // Submit the stored form
            }
            hideCustomConfirmModal(); // Hide the custom modal
        });
    });

    // Function to show the custom confirmation modal
    function showCustomConfirmModal() {
        const modal = document.getElementById('customConfirmModal');
        modal.style.display = 'flex'; // Use flex to center it
    }

    // Function to hide the custom confirmation modal
    function hideCustomConfirmModal() {
        const modal = document.getElementById('customConfirmModal');
        modal.style.display = 'none';
        currentVerifyForm = null; // Reset the stored form
    }
</script>
@endpush