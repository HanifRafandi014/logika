@extends('layouts.main')
@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection
@section('content')
<div class="container" style="font-size: 11px;">
    <form method="POST" action="{{ route('pembina.profil_pembina') }}">
        @csrf
        <h3 style="font-size: 11px;">Data Profil Pembina:</h3>

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
            <input type="text" class="form-control" id="nama" name="nama" value="{{ $pembina->nama ?? '' }}" style="font-size: 11px;">
        </div>
        <div class="form-group">
            <label for="nip">NIP</label>
            <input type="text" class="form-control" id="nip" name="alamat" value="{{ $pembina->alamat ?? '' }}" style="font-size: 11px;">
        </div>
        <div class="form-group">
            <label for="tanggal_lahir">Tanggal Lahir</label>
            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="{{ $pembina->tanggal_lahir ?? '' }}" style="font-size: 11px;">
        </div>
        <div class="form-group">
            <label for="jenis_kelamin">Jenis Kelamin</label>
            <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" style="font-size: 11px;">
                <option value="L" {{ (isset($pembina->jenis_kelamin) && $pembina->jenis_kelamin == 'L') ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ (isset($pembina->jenis_kelamin) && $pembina->jenis_kelamin == 'P') ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>
        <div class="form-group">
            <label for="no_hp">Nomor Handphone</label>
            <input type="text" class="form-control" id="no_hp" name="no_hp" value="{{ $pembina->no_hp ?? '' }}" style="font-size: 11px;">
        </div>
        <div class="form-group">
            <label for="kwartir_cabang">Kwartir Cabang</label>
            <input type="text" class="form-control" id="kwartir_cabang" name="kwartir_cabang" value="{{ $pembina->kwartir_cabang ?? '' }}" style="font-size: 11px;">
        </div>
        <div class="form-group">
            <label for="pangkalan">Nama Pangkalan</label>
            <input type="text" class="form-control" id="pangkalan" name="pangkalan" value="{{ $pembina->pangkalan ?? '' }}" style="font-size: 11px;">
        </div>
        <div class="form-group">
            <label for="pengalaman_pembina">Pengalaman pembina</label>
            <input type="text" class="form-control" id="pengalaman_pembina" name="pengalaman_pembina" value="{{ $pembina->pengalaman_pembina ?? '' }}" style="font-size: 11px;">
        </div>
        <div class="form-group">
            <label for="pekerjaan">Pekerjaan</label>
            <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" value="{{ $pembina->pekerjaan ?? '' }}" style="font-size: 11px;">
        </div>
        <button type="submit" class="btn btn-primary" style="font-size: 11px;">Simpan</button>
    </form>

    @if(isset($pembina))
    <div class="mt-5">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td><strong>Nama</strong></td>
                    <td>{{ $pembina->nama }}</td>
                </tr>
                <tr>
                    <td><strong>Alamat</strong></td>
                    <td>{{ $pembina->alamat }}</td>
                </tr>
                <tr>
                    <td><strong>Tanggal Lahir</strong></td>
                    <td>{{ $pembina->tanggal_lahir }}</td>
                </tr>
                <tr>
                    <td><strong>Jenis Kelamin</strong></td>
                    <td>{{ $pembina->jenis_kelamin }}</td>
                </tr>
                <tr>
                    <td><strong>Nomor Handphone</strong></td>
                    <td>{{ $pembina->no_hp }}</td>
                </tr>
                <tr>
                    <td><strong>Kwartir Cabang</strong></td>
                    <td>{{ $pembina->kwartir_cabang }}</td>
                </tr>
                <tr>
                    <td><strong>Nama Pangkalan</strong></td>
                    <td>{{ $pembina->pangkalan }}</td>
                </tr>
                <tr>
                    <td><strong>Pengalaman pembina</strong></td>
                    <td>{{ $pembina->pengalaman_pembina }}</td>
                </tr>
                <tr>
                    <td><strong>Pekerjaan</strong></td>
                    <td>{{ $pembina->pekerjaan }}</td>
                </tr>
                <tr>
                    <td><strong>Mata Lomba</strong></td>
                    <td>{{optional($pembina->mata_lomba)->nama ?? 'Belum Diisi'}}</td>
            
                </tr>
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection