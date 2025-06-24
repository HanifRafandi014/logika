@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.alumni') {{-- Menggunakan sidebar alumni --}}
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card">
      <div class="card-header">
        <h4>Buat Event Baru</h4>
      </div>
      <div class="card-body">
        <form action="{{ route('event.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="form-group">
            <label for="jenis_event">Jenis Event</label>
            <input type="text" name="jenis_event" id="jenis_event" class="form-control @error('jenis_event') is-invalid @enderror" value="{{ old('jenis_event') }}" required>
            @error('jenis_event')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <div class="form-group">
            <label for="judul">Judul</label>
            <input type="text" name="judul" id="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul') }}" required>
            @error('judul')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <div class="form-group">
            <label for="gambar">Gambar Event</label>
            <input type="file" name="gambar" id="gambar" class="form-control-file @error('gambar') is-invalid @enderror" required>
            @error('gambar')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="5" required>{{ old('keterangan') }}</textarea>
            @error('keterangan')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <button type="submit" class="btn btn-success">Simpan</button>
          <a href="{{ route('event.index') }}" class="btn btn-secondary">Batal</a>
        </form>
      </div>
    </div>
  </div>
@endsection
