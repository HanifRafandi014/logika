{{-- resources/views/pembayaran_iuran/create.blade.php --}}
@extends('layouts.main')
@section('sidebar')
    @include('layouts.sidebar.orang_tua')
@endsection
@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Tambah Pembayaran Iuran</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('pembayaran-iuran.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="orang_tua_nama" class="form-label">Nama Orang Tua</label>
                    <input type="text" class="form-control" id="orang_tua_nama" value="{{ $orangTuaLogin->nama ?? 'N/A' }}" readonly>
                    <input type="hidden" name="orang_tua_id" value="{{ $orangTuaLogin->id ?? '' }}">
                    @error('orang_tua_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="siswa_id" class="form-label">Nama Siswa</label>
                    <select class="form-control" id="siswa_id" name="siswa_id" required>
                        <option value="">Pilih Siswa</option>
                        @foreach ($siswas as $siswa)
                            <option value="{{ $siswa->id }}" {{ old('siswa_id') == $siswa->id ? 'selected' : '' }}>
                                {{ $siswa->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('siswa_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="bulan_bayar" class="form-label">Bulan Bayar</label>
                    <input type="date" class="form-control" id="bulan_bayar" name="bulan_bayar" value="{{ old('bulan_bayar') }}" required>
                    @error('bulan_bayar')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jumlah" class="form-label">Jumlah</label>
                    <input type="number" class="form-control" id="jumlah" name="jumlah" value="{{ old('jumlah') }}" required>
                    @error('jumlah')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="bukti_bayar" class="form-label">Bukti Bayar</label>
                    <input type="file" class="form-control" id="bukti_bayar" name="bukti_bayar" accept="image/*">
                    @error('bukti_bayar')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="status_pembayaran" name="status_pembayaran" value="1" {{ old('status_pembayaran') ? 'checked' : '' }}>
                    <label class="form-check-label" for="status_pembayaran">Status Pembayaran (Centang jika Lunas)</label>
                    @error('status_pembayaran')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('pembayaran-iuran.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
