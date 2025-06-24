<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <style>
      /* Input search styling */
      .column-search {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
      }
    
      .column-search:focus {
        border-color: #66afe9;
        outline: none;
        box-shadow: 0 0 5px rgba(102, 175, 233, 0.6);
      }
      .event-image {
          max-width: 100px; /* Ukuran gambar di tabel */
          height: auto;
          border-radius: 8px;
          object-fit: cover;
      }
    </style>
</head>
 
@extends('layouts.main')
 
@section('sidebar')
    @include('layouts.sidebar.alumni') {{-- Menggunakan sidebar alumni --}}
@endsection
 
@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Event Saya</h4>
            <div class="d-flex ms-auto">
              <a href="{{ route('event.create') }}" class="btn btn-primary btn-sm me-2" title="Tambah Event">
                  <i class="fa fa-plus-square" aria-hidden="true"></i>
              </a>
          </div>     
        </div>     
      <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="table-responsive">
          <table id="multi-filter-select" class="display table table-striped table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>Jenis Event</th>
                <th>Judul</th>
                <th>Gambar</th>
                <th>Keterangan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
                @forelse ($events as $index => $event)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $event->jenis_event }}</td>
                  <td>{{ $event->judul }}</td>
                  <td>
                      @if ($event->gambar)
                          <img src="{{ asset('storage/' . $event->gambar) }}" alt="{{ $event->judul }}" class="event-image">
                      @else
                          Tidak ada gambar
                      @endif
                  </td>
                  <td>{{ Str::limit($event->keterangan, 50) }}</td>
                  <td>
                    <a href="{{ route('event.edit', $event->id) }}" class="btn btn-sm btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('event.destroy', $event->id) }}" method="POST" style="display:inline;">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus event ini? Ini akan menghapus gambar terkait juga.')" title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  </td>              
                </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Anda belum membuat event apapun.</td>
                    </tr>
                @endforelse
            </tbody>
          </table>

        </div>
      </div>
    </div>
</div>
@endsection
 
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
      var table = $('#multi-filter-select').DataTable({
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 5
      });
    });
</script>
