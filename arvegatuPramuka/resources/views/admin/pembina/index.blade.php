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

        /* Styling for filter dropdowns */
        .filter-container {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px; /* Spacing between filter groups */
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 5px; /* Spacing between label and select */
        }
        .filter-group label {
            white-space: nowrap;
        }
        .filter-group select {
            padding: 4px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        /* Custom styling for DataTables layout */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            display: inline-block;
            vertical-align: middle;
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
        }

        .dataTables_wrapper .dataTables_filter label input {
            border-radius: 8px;
            border: 1px solid #ccc;
            padding: 6px 10px;
            font-size: 14px;
        }

        /* Clearfix for dataTables_wrapper to prevent layout issues */
        .dataTables_wrapper:after {
            content: "";
            display: table;
            clear: both;
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
            <h4 class="card-title">Data Pembina</h4>
            <div class="d-flex ms-auto">
                {{-- <a href="{{ url('admin/pembina/data-pembina/export') }}" class="btn btn-info btn-sm me-2" title="Export Data">
                    <i class="fas fa-download"></i> Export Data
                </a> --}}
                <a href="{{ route('data-pembina.create') }}" class="btn btn-primary btn-sm me-2" title="Tambah Pembina">
                    <i class="fa fa-plus-square" aria-hidden="true"></i> Tambah
                </a>
                <a href="{{ route('admin.pembina.import-form-pembina') }}" class="btn btn-success btn-sm" title="Import Data">
                    <i class="fas fa-file-excel"></i> Import
                </a>
            </div>    
        </div>    
      <div class="card-body">
        <div class="filter-container">
            <div class="filter-group">
                <label for="statusFilter">Status:</label>
                <select id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="Pembina PA">Pembina PA</option>
                    <option value="Pembina PI">Pembina PI</option>
                </select>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
          <table id="multi-filter-select" class="display table table-striped table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Pembina</th>
                <th>Kelas</th>
                <th>NIP</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
                @foreach ($pembinas as $index => $pembina)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $pembina->nama ?? 'N/A' }}</td> 
                  <td>{{ $pembina->kelas }}</td> 
                  <td>{{ $pembina->nip ?? 'N/A' }}</td> 
                  <td>{{ $pembina->kategori }}</td>
                  <td>
                      @if ($pembina->status == 1)
                          Pembina PA
                      @else
                          Pembina PI
                      @endif
                  </td>
                  <td>
                    <a href="{{ route('data-pembina.edit', $pembina->id) }}" class="btn btn-sm btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('data-pembina.destroy', $pembina->id) }}" method="POST" style="display:inline;">
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
        // Initialize DataTable
        var table = $('#multi-filter-select').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5,
        });
    });
</script>