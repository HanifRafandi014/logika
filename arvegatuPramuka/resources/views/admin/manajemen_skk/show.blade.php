@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.admin')
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Detail Data SKK</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Jenis SKK :</strong>
                </div>
                <div class="col-md-8">
                    {{ $skk->jenis_skk }}
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <strong>Logo :</strong>
                </div>
                <div class="col-md-8">
                    @if($skk->logo)
                        <img src="{{ asset('storage/' . $skk->logo) }}" alt="Logo SKK" style="max-width: 70px; height: auto; border-radius: 8px;">
                    @else
                        Tidak ada logo
                    @endif
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <strong>Kompetensi Dasar SKK :</strong>
                </div>
                <div class="col-md-8">
                    {{ $skk->kompetensi_dasar }}
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <strong>Item Pencapaian SKK :</strong>
                </div>
                <div class="col-md-8">
                    {{ $skk->keterangan_skk }}
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <strong>Tingkatan :</strong>
                </div>
                <div class="col-md-8">
                    {{ $skk->tingkatan }}
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <strong>Kelompok :</strong>
                </div>
                <div class="col-md-8">
                    {{ $skk->kelompok }}
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <strong>Kategori :</strong>
                </div>
                <div class="col-md-8">
                    {{ $skk->kategori }}
                </div>
            </div>
            <hr>
            <a href="{{ route('manajemen_skk.index') }}" class="btn btn-secondary mt-3">Kembali ke Daftar SKK</a>
        </div>
    </div>
</div>
@endsection
