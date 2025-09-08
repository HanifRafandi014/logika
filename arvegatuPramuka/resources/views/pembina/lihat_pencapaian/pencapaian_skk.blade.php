@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Pencapaian SKK</h4>
            <div class="d-flex ms-auto">
                <a href="{{ route('pencapaian-skk.export') }}" class="btn btn-success btn-sm" title="Export Data">
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
                            <th>Jenis SKK</th>
                            <th>Tingkatan</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pencapaianFinal as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item['nama'] }}</td>
                            <td>{{ $item['kelas'] }}</td>
                            <td>{{ $item['nisn'] }}</td>
                            <td>{{ $item['jenis_skk'] }}</td>
                            <td>{{ $item['tingkatan'] }}</td>
                            <td>{{ $item['status'] }}</td>
                            <td>{{ $item['tanggal'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#multi-filter-select').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5
        });
    });
</script>
@endpush
