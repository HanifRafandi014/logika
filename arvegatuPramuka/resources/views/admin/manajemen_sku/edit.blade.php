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
                    <label for="keterangan_sku" class="form-label">Keterangan SKU</label>
                    {{-- Ubah input text menjadi textarea --}}
                    <textarea class="form-control" id="keterangan_sku" name="keterangan_sku" rows="5" required>{{ old('keterangan_sku', $sku->keterangan_sku) }}</textarea>
                    @error('keterangan_sku')
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

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('manajemen_sku.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection