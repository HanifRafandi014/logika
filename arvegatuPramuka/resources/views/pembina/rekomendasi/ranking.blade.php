@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>

<div class="col-md-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Ranking Kompetisi</h4>
            <a href="{{ route('pembina.rekomendasi.index') }}" class="btn btn-secondary btn-sm" title="Kembali">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="ranking-table" class="display table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Jenis Lomba</th>
                            <th>Peringkat</th>
                            <th>Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rankingData as $index => $ranking)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $ranking['nama_siswa'] ?? 'Tidak diketahui' }}</td>
                                <td>{{ $ranking['lomba'] ?? 'Tidak diketahui' }}</td>
                                <td>{{ $ranking['peringkat'] ?? '-' }}</td>
                                <td>{{ $ranking['skor'] ?? '-' }}</td>
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
        $('#ranking-table').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 5
        });
    });
</script>
@endsection
