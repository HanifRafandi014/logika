@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <style>
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

<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Data Normalisasi Siswa</h4>
            <a href="{{ route('pembina.rekomendasi.index') }}" class="btn btn-secondary btn-sm" title="Kembali">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="multi-filter-select" class="display table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Siswa</th>
                            <th>Nama Siswa</th>
                            @if(count($normalizedData) > 0)
                                @foreach(array_keys($normalizedData[0]) as $key)
                                    @if(!in_array($key, ['ID Siswa', 'Nama Siswa']))
                                        <th>{{ $key }}</th>
                                    @endif
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($normalizedData as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['ID Siswa'] }}</td>
                            <td>{{ $row['Nama Siswa'] }}</td>
                            @foreach($row as $key => $value)
                                @if(!in_array($key, ['ID Siswa', 'Nama Siswa']))
                                    <td>{{ number_format($value, 4) }}</td>
                                @endif
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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
@endsection
