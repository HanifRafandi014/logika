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
    </style>
  </head>
  
  @extends('layouts.main')
  
  @section('sidebar')
    @include('layouts.sidebar.admin')
  @endsection
  
  @section('content')
  <div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Data Siswa</h4>
            <div class="d-flex ms-auto">
              <a href="{{ route('admin.siswa.download-template') }}" class="btn btn-info btn-sm me-2" title="Download Template">
                  <i class="fas fa-download"></i>
              </a>
              <a href="{{ route('data-siswa.create') }}" class="btn btn-primary btn-sm me-2" title="Tambah Siswa">
                  <i class="fa fa-plus-square" aria-hidden="true"></i>
              </a>
              <a href="{{ route('admin.siswa.import-form-siswa') }}" class="btn btn-success btn-sm" title="Import">
                  <i class="fas fa-file-excel"></i>
              </a>
            </div> 
        </div> 
      <div class="card-body">
        <div class="table-responsive">
          <table id="multi-filter-select" class="display table table-striped table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>NISN</th>
                <th>Jenis Kelamin</th>
                <th>Angkatan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
                @foreach ($siswas as $index => $siswa)
                <tr>
                  <td>{{ $index + 1 }}</td> <!-- Nomor Urut -->
                  <td>{{ $siswa->nama }}</td>
                  <td>{{ $siswa->kelas }}</td>
                  <td>{{ $siswa->nisn }}</td>
                  <td>
                      @if ($siswa->jenis_kelamin == 1)
                          Laki-laki
                      @else
                          Perempuan
                      @endif
                  </td>
                  <td>{{ $siswa->angkatan }}</td>
                  <td>
                    {{-- Tombol Detail/Show --}}
                    <a href="{{ route('data-siswa.show', $siswa->id) }}" class="btn btn-sm btn-info" title="Detail">
                        <i class="fas fa-eye"></i>
                    </a>
                    {{-- Tombol Edit --}}
                    <a href="{{ route('data-siswa.edit', $siswa->id) }}" class="btn btn-sm btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    {{-- Form Hapus --}}
                    <form action="{{ route('data-siswa.destroy', $siswa->id) }}" method="POST" style="display:inline;">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  </td>           
                </tr>
                @endforeach
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
