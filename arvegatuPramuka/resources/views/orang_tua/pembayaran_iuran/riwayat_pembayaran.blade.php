@extends('layouts.main') {{-- Ensure this path is correct for your main layout --}}
@section('sidebar')
    @include('layouts.sidebar.orang_tua') {{-- Ensure this path is correct for your parent sidebar --}}
@endsection

@section('content')
<style>
    /* Add some basic styling to mimic the image look */
    .payment-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
        color: #333; /* Darker text for better readability */
    }
    .payment-list-item:last-child {
        border-bottom: none; /* No border for the last item */
    }
    .payment-details {
        flex-grow: 1;
    }
    .payment-amount {
        font-weight: bold;
        font-size: 1.1em; /* Slightly larger for amount */
        color: #333; /* Darker color for amount */
    }
    .payment-date {
        font-size: 0.9em;
        color: #888; /* Lighter color for date */
    }
    .payment-status {
        font-weight: bold;
        color: green; /* Default to green for "Lunas" */
        display: flex;
        align-items: center;
    }
    .payment-status.unpaid {
        color: orange; /* Orange for "Belum Lunas" */
    }
    .arrow-icon {
        margin-left: 10px;
        color: #ccc; /* Lighter color for the arrow */
    }
    .card {
        box-shadow: none; /* Remove card shadow if not desired */
        border: none; /* Remove card border */
    }
    .card-header {
        background-color: transparent; /* Transparent header background */
        border-bottom: none; /* No header border */
        font-weight: bold;
        font-size: 1.2em; /* Larger header text */
        padding-left: 0; /* Align with list items */
    }
    .card-body {
        padding: 0; /* Remove default card body padding */
    }
</style>

<div class="container">
    {{-- Remove the card header and tabs if you want a cleaner look like the image --}}
    {{-- <div class="card-header"> --}}
    {{--     Riwayat Pembayaran --}}
    {{-- </div> --}}

    <div class="d-flex align-items-center mb-3">
        <a href="{{ url()->previous() }}" class="text-decoration-none text-dark me-2">
            <i class="fas fa-arrow-left fa-lg"></i> {{-- Assuming you have Font Awesome for the arrow icon --}}
        </a>
        <h4 class="mb-0">Riwayat Pembayaran</h4>
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
                                    Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                                </div>
                                <div class="payment-date">
                                    {{ \Carbon\Carbon::parse($pembayaran->created_at)->format('d M Y') }}
                                </div>
                                {{-- Optionally display student name if desired, but image doesn't show it explicitly --}}
                                {{-- <div class="payment-student">
                                    Untuk Siswa: {{ $pembayaran->siswa->nama ?? 'N/A' }}
                                </div> --}}
                            </div>
                            <div class="payment-status @if(!$pembayaran->status_pembayaran) unpaid @endif">
                                @if($pembayaran->status_pembayaran)
                                    Lunas
                                @else
                                    Belum Lunas
                                @endif
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