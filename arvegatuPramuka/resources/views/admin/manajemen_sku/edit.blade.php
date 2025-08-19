@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Edit Data SKU</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('manajemen_sku.update', $sku->id) }}" method="POST">
                @csrf
                @method('PUT') 
                <div class="mb-3">
                    <label for="keterangan_sku" class="form-label">Kompetensi Dasar SKU</label>
                    {{-- Ubah input text menjadi textarea --}}
                    <textarea class="form-control" id="keterangan_sku" name="keterangan_sku" rows="5" required>{{ old('keterangan_sku', $sku->keterangan_sku) }}</textarea>
                    @error('keterangan_sku')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="item_pencapaian_sku" class="form-label">Item Pencapaian SKU</label>
                    {{-- Ubah input text menjadi textarea --}}
                    <textarea class="form-control" id="item_pencapaian_sku" name="item_pencapaian_sku" rows="5" required>{{ old('item_pencapaian_sku', $sku->item_pencapaian_sku) }}</textarea>
                    @error('item_pencapaian_sku')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tingkatan" class="form-label">Tingkatan</label>
                    <select class="form-control" id="tingkatan" name="tingkatan" required>
                        <option value="">Pilih Tingkatan</option>
                        @foreach($tingkatans as $tingkatan)
                            <option value="{{ $tingkatan }}" {{ old('tingkatan', $sku->tingkatan) == $tingkatan ? 'selected' : '' }}>{{ $tingkatan }}</option>
                        @endforeach
                    </select>
                    @error('tingkatan')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" title="Update">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <a href="{{ route('manajemen_sku.index') }}" class="btn btn-secondary" title="Kembali">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </form>
        </div>
    </div>
</div>
@endsection