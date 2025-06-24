@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Tambah Data SKK</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('manajemen_skk.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="keterangan_skk" class="form-label">Keterangan SKK</label>
                    {{-- Ubah input text menjadi textarea --}}
                    <textarea class="form-control" id="keterangan_skk" name="keterangan_skk" rows="5" required>{{ old('keterangan_skk') }}</textarea>
                    @error('keterangan_skk')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label>Jenis SKK</label>
                    <input type="text" name="jenis_skk" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="tingkatan" class="form-label">Tingkatan</label>
                    <select class="form-control" id="tingkatan" name="tingkatan" required>
                        <option value="">Pilih Tingkatan</option>
                        @foreach($tingkatans as $tingkatan)
                            <option value="{{ $tingkatan }}" {{ old('tingkatan') == $tingkatan ? 'selected' : '' }}>{{ $tingkatan }}</option>
                        @endforeach
                    </select>
                    @error('tingkatan')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('manajemen_skk.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
