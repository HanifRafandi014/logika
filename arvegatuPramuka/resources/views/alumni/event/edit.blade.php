@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.alumni') {{-- Menggunakan sidebar alumni --}}
@endsection

@section('content')
<div class="col-md-8 offset-md-2">
    <div class="card">
      <div class="card-header">
        <h4>Edit Event</h4>
      </div>
      <div class="card-body">
        <form action="{{ route('event.update', $event->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <div class="form-group">
            <label for="jenis_event">Jenis Event</label>
            <input type="text" name="jenis_event" id="jenis_event" class="form-control @error('jenis_event') is-invalid @enderror" value="{{ old('jenis_event', $event->jenis_event) }}" required>
            @error('jenis_event')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <div class="form-group">
            <label for="judul">Judul</label>
            <input type="text" name="judul" id="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $event->judul) }}" required>
            @error('judul')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <div class="form-group">
            <label for="gambar">Gambar Event</label>
            @if ($event->gambar)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $event->gambar) }}" alt="Current Image" style="max-width: 200px; height: auto; border-radius: 8px;">
                    <small class="form-text text-muted">Gambar saat ini</small>
                </div>
            @endif
            <input type="file" name="gambar" id="gambar" class="form-control-file @error('gambar') is-invalid @enderror">
            <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah gambar.</small>
            @error('gambar')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="5" required>{{ old('keterangan', $event->keterangan) }}</textarea>
            @error('keterangan')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
          </div>
          <button type="submit" class="btn btn-primary" title="Update">
            <i class="fas fa-sync-alt"></i>
          </button>
          <a href="{{ route('event.index') }}" class="btn btn-secondary" title="Kembali">
            <i class="fas fa-arrow-left"></i>
          </a>
        </form>
      </div>
    </div>
  </div>
@endsection
