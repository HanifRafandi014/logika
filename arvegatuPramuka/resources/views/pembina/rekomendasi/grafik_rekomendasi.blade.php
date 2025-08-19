@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-primary">
            <i class="fas fa-chart-pie mr-2"></i> Grafik Rekomendasi Siswa per Kategori Lomba
        </h3>
        <div class="d-flex gap-2">
            <a href="{{ route('pembina.rekomendasi.export') }}" class="btn btn-success" title="Export Excel">
                <i class="fas fa-file-excel mr-1"></i>
            </a>
            <a href="{{ route('pembina.rekomendasi.index') }}" class="btn btn-secondary" title="Batal">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
    </div>

    @if($silhouette_score !== null && $db_index !== null)
        <div class="d-flex justify-content-center mb-4 flex-wrap">
            <span class="badge bg-primary fs-6 px-2 py-2 shadow-sm">
                Silhouette Score: <strong>{{ $silhouette_score }}</strong>
            </span>
            <span class="badge bg-info fs-6 px-2 py-2 shadow-sm">
                Davies-Bouldin Index: <strong>{{ $db_index }}</strong>
            </span>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="text-center text-secondary mb-4">📈 Distribusi Siswa per Kategori</h5>
            <div class="d-flex justify-content-center">
                <canvas id="pieChart" width="400" height="400" style="max-width: 50%;"></canvas>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="text-center text-secondary mb-4">📋 Detail Data Rekomendasi</h5>
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered" id="kategoriTable" style="width: 100%;">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>No</th>
                            <th>Kategori Lomba</th>
                            <th>Jumlah</th>
                            <th>Cluster</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tableData as $kategori => $jumlah)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $kategori }}</td>
                            <td class="text-center">{{ $jumlah }}</td>
                            <td class="text-center">Cluster {{ $clusters[$kategori] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Required CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = {!! json_encode($labels) !!};
    const counts = {!! json_encode($counts) !!};

    if (!labels.length || !counts.length || counts.every(v => v === 0)) {
        console.warn("Data pie chart kosong atau semua bernilai 0");
        return;
    }

    const canvas = document.getElementById('pieChart');
    const ctx = canvas?.getContext('2d');

    if (!ctx) {
        console.error("Canvas context not found");
        return;
    }

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: counts,
                backgroundColor: [
                    '#007bff', '#dc3545', '#ffc107', '#28a745', '#6610f2',
                    '#20c997', '#fd7e14', '#6f42c1', '#17a2b8', '#343a40',
                    '#e83e8c', '#6c757d', '#198754', '#0dcaf0', '#f8f9fa',
                    '#adb5bd', '#ff6b6b', '#ff9f43', '#1dd1a1', '#00d2d3'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        font: {
                            size: 13
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${context.label}: ${context.parsed} siswa (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // ✅ Aktifkan DataTables di sini
    $('#kategoriTable').DataTable({
        paging: true,
        pageLength: 5,
        lengthChange: false,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        language: {
            paginate: {
                previous: "←",
                next: "→"
            },
            zeroRecords: "Tidak ada data yang tersedia",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            search: "Cari:",
            emptyTable: "Tidak ada data tersedia"
        }
    });
});

</script>

<style>
    .badge {
        font-size: 1rem;
        border-radius: 8px;
    }

    .card {
        border: none;
        border-radius: 12px;
    }

    .table th {
        font-weight: 600;
    }

    .dataTables_filter input {
        border-radius: 6px;
        padding: 6px 12px;
        border: 1px solid #ccc;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 6px 12px;
        margin: 0 2px;
        border-radius: 6px;
        background: #f8f9fa;
        border: 1px solid #ddd;
        color: #333;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #0d6efd;
        color: #fff !important;
    }
</style>
@endsection
