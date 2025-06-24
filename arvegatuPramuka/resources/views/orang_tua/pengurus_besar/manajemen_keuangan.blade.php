@extends('layouts.main') {{-- Sesuaikan dengan layout Anda --}}

@section('sidebar')
    @include('layouts.sidebar.orang_tua') {{-- Sesuaikan dengan sidebar pengurus besar --}}
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Manajemen Keuangan Paguyuban Besar</h4>
        </div>
        <div class="card-body">
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

            <div class="alert alert-info">
                <h5>Saldo Saat Ini: Rp {{ number_format($saldoSaatIni, 0, ',', '.') }}</h5>
            </div>

            <h5 class="mt-4">Catat Pengeluaran Baru</h5>
            <form action="{{ route('orang_tua.pengurus_besar.store_pengeluaran') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="jumlah_pengeluaran" class="form-label">Jumlah (Rp)</label>
                        <input type="number" class="form-control" id="jumlah_pengeluaran" name="jumlah" value="{{ old('jumlah') }}" required min="1">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="kategori_pengeluaran" class="form-label">Kategori</label>
                        <input type="text" class="form-control" id="kategori_pengeluaran" name="kategori" value="{{ old('kategori') }}" placeholder="Contoh: Hotel, ATK, Honor" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tanggal_transaksi" class="form-label">Tanggal Transaksi</label>
                        <input type="date" class="form-control" id="tanggal_transaksi" name="tanggal_transaksi" value="{{ old('tanggal_transaksi', \Carbon\Carbon::now()->toDateString()) }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="bukti_transaksi" class="form-label">Bukti Transaksi (Opsional)</label>
                    <input type="file" class="form-control" id="bukti_transaksi" name="bukti_transaksi" accept="image/*">
                    <small class="form-text text-muted">Unggah foto bukti pengeluaran.</small>
                </div>
                <button type="submit" class="btn btn-danger">Catat Pengeluaran</button>
            </form>
        </div>
    </div>
</div>
@endsection