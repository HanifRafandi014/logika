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
            <h4 class="card-title">Data Orang Tua</h4>
            <div class="d-flex ms-auto">
              <a href="{{ route('data-orang-tua.create') }}" class="btn btn-primary btn-sm me-2" title="Tambah Orang Tua">
                  <i class="fa fa-plus-square" aria-hidden="true"></i>
              </a>
              <a href="{{ route('admin.orang_tua.import-form-orang-tua') }}" class="btn btn-success btn-sm" title="Import Data">
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
                <th>Nama Orang Tua</th>
                <th>No Handphone</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
                @foreach ($orangTuas as $index => $orangTua)
                <tr>
                  <td>{{ $index + 1 }}</td> <!-- Nomor Urut -->
                  <td>{{ $orangTua->nama }}</td>
                  <td>{{ $orangTua->no_hp }}</td>
                  <td>{{ $orangTua->siswa->nama }}</td>
                  <td>{{ $orangTua->siswa->kelas }}</td>
                  <td>{{ $orangTua->status }}</td>
                  <td>
                    <a href="{{ route('data-orang-tua.show', $orangTua->id) }}" class="btn btn-sm btn-info" title="Detail">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('data-orang-tua.edit', $orangTua->id) }}" class="btn btn-sm btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('data-orang-tua.destroy', $orangTua->id) }}" method="POST" style="display:inline;">
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
  