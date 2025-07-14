@extends('layouts.main') {{-- Ensure this path is correct for your main layout --}}
@section('sidebar')
    @include('layouts.sidebar.orang_tua') {{-- Ensure this path is correct for your parent sidebar --}}
@endsection

@section('content')
<style>
    /* Styling for the overall container and card */
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

    /* Header styling */
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

    /* Payment list item styling */
    .payment-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px; /* Consistent padding */
        border-bottom: 1px solid #eee;
        color: #333;
        background-color: #fff; /* White background for list items */
    }
    .payment-list-item:last-child {
        border-bottom: none; /* No border for the last item */
    }
    .payment-details {
        flex-grow: 1;
    }
    .payment-amount {
        font-weight: bold;
        font-size: 1.2em; /* Larger for amount */
        color: #007bff; /* Blue for emphasis, matching image */
        margin-bottom: 5px; /* Space between amount and date */
    }
    .payment-date {
        font-size: 0.9em;
        color: #888;
    }
    .payment-status-container {
        display: flex;
        align-items: center;
    }
    .payment-status-text {
        font-weight: bold;
        font-size: 0.95em;
    }
    .payment-status-text.verified {
        color: green;
    }
    .payment-status-text.unverified {
        color: orange; /* Orange for Belum Diverifikasi */
    }
    .arrow-icon {
        margin-left: 10px;
        color: #ccc;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .payment-list-item {
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }
        .payment-status-container {
            margin-top: 10px;
        }
    }
</style>

<div class="container">
    <div class="header-section">
        <a href="{{ url()->previous() }}" class="back-arrow">
            <i class="fas fa-arrow-left"></i> {{-- Assuming you have Font Awesome for the arrow icon --}}
        </a>
        <h4 class="page-title">Riwayat Pembayaran Paguyuban Orang Tua</h4>
    </div>

    <div class="card">
        <div class="card-body">
            @if($riwayatPembayarans->isEmpty())
                <p class="text-center py-4">Belum ada riwayat pembayaran.</p>
            @else
                <div class="payment-history-list">
                    @foreach($riwayatPembayarans as $pembayaran)
                        <div class="payment-list-item">
                            <div class="payment-details">
                                <div class="payment-amount">
                                    {{-- Menggunakan total_bayar_final dari accessor model --}}
                                    Rp {{ number_format($pembayaran->total_bayar_final, 0, ',', '.') }}
                                </div>
                                <div class="payment-date">
                                    {{-- Menampilkan tanggal dibuatnya record pembayaran --}}
                                    {{ \Carbon\Carbon::parse($pembayaran->created_at)->translatedFormat('d M Y') }}
                                </div>
                                {{-- Opsional: Tampilkan bulan-bulan yang dibayar jika ingin detail lebih --}}
                                {{-- <div class="payment-months">
                                    @if($pembayaran->bulan_bayar)
                                        @php
                                            $decodedMonths = json_decode($pembayaran->bulan_bayar, true);
                                        @endphp
                                        @if(is_array($decodedMonths))
                                            Bulan: {{ implode(', ', $decodedMonths) }}
                                        @else
                                            Bulan: N/A
                                        @endif
                                    @else
                                        Bulan: N/A
                                    @endif
                                </div> --}}
                            </div>
                            <div class="payment-status-container">
                                <div class="payment-status-text @if($pembayaran->status_pembayaran) verified @else unverified @endif">
                                    @if($pembayaran->status_pembayaran)
                                        Sudah Diverifikasi
                                    @else
                                        Belum Diverifikasi
                                    @endif
                                </div>
                                <i class="fas fa-chevron-right arrow-icon"></i> {{-- Font Awesome arrow icon --}}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
