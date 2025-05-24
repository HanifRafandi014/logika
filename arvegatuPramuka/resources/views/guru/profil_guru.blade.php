@extends('layouts.main')
@section('sidebar')
    @include('layouts.sidebar.guru')
@endsection
@section('content')
<div class="container" style="font-size: 11px;">
    <form method="POST" action="{{ route('guru.profil.update') }}">
        @csrf
        <h3>Data Profil Guru:</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="form-group">
            <label for="nama">Nama</label>
            <input type="text" class="form-control" id="nama" name="nama" value="{{ $guru->nama ?? '' }}">
        </div>
        <div class="form-group">
            <label for="mata_pelajaran">Mata Pelajaran</label>
            <input type="text" class="form-control" id="mata_pelajaran" name="mata_pelajaran" value="{{ $guru->mata_pelajaran ?? '' }}">
        </div>
        <div class="form-group">
            <label for="nip">NIP</label>
            <input type="text" class="form-control" id="nip" name="nip" value="{{ $guru->nip ?? '' }}">
        </div>
        
        <div class="form-group">
            <label for="pembina_pramuka">Pembina Pramuka</label>
            <select class="form-control" id="pembina_pramuka" name="pembina_pramuka">
                <option value="0" {{ (isset($guru->pembina_pramuka) && $guru->pembina_pramuka == '0') ? 'selected' : '' }}>Tidak</option>
                <option value="1" {{ (isset($guru->pembina_pramuka) && $guru->pembina_pramuka == '1') ? 'selected' : '' }}>Ya</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" title="Simpan">
            <i class="fa fa-bookmark" aria-hidden="true"></i>
        </button>
    </form>

    @if(isset($guru))
    <div class="mt-5">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td><strong>Nama</strong></td>
                    <td>{{ $guru->nama }}</td>
                </tr>
                <tr>
                    <td><strong>Mata pelajaran</strong></td>
                    <td>{{ $guru->mata_pelajaran }}</td>
                </tr>
                <tr>
                    <td><strong>NIP</strong></td>
                    <td>{{ $guru->nip }}</td>
                </tr>
                <tr>
                    <td><strong>Pembina Pramuka</strong></td>
                    <td>{{ $guru->pembina_pramuka }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection