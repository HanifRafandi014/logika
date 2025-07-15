@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 30px;">📊 Grafik Rekomendasi Siswa per Kategori Lomba</h2>

        <!-- Export Button -->
        <div style="text-align: right; margin-bottom: 20px;">
            <a href="{{ route('pembina.rekomendasi.export') }}" class="btn btn-success">
                ⬇️ Export ke Excel
            </a>
        </div>

        <!-- Pie Chart Section -->
        <div class="row" style="margin-bottom: 30px;">
            <div class="col-12">
                <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto;">
                    <h4 style="text-align: center; margin-bottom: 25px; color: #333;">Distribusi Siswa per Kategori</h4>
                    <div style="display: flex; justify-content: center;">
                        <canvas id="pieChart" style="max-height: 450px; max-width: 450px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="row">
            <div class="col-12">
                <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h4 style="text-align: center; margin-bottom: 25px; color: #333;">Detail Data Rekomendasi</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="kategoriTable" style="width: 100%;">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 10%;">No</th>
                                    <th style="width: 45%;">Kategori Lomba</th>
                                    <th style="width: 20%;">Jumlah</th>
                                    <th style="width: 25%;">Cluster</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tableData as $kategori => $jumlah)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $kategori }}</td>
                                        <td style="text-align: center;">{{ $jumlah }}</td>
                                        <td style="text-align: center;">Cluster {{ $clusters[$kategori] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Pie Chart
            const ctx = document.getElementById('pieChart').getContext('2d');
            const pieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: {!! json_encode($labels) !!},
                    datasets: [{
                        label: 'Jumlah Siswa',
                        data: {!! json_encode($counts) !!},
                        backgroundColor: [
                            '#007bff', '#dc3545', '#ffc107', '#28a745', '#6610f2',
                            '#20c997', '#fd7e14', '#6f42c1', '#17a2b8', '#343a40',
                            '#e83e8c', '#6c757d', '#198754', '#0dcaf0', '#f8f9fa',
                            '#adb5bd', '#ff6b6b', '#ff9f43', '#1dd1a1', '#00d2d3'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 15,
                                padding: 20,
                                font: {
                                    size: 13
                                },
                                usePointStyle: true
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
                    },
                    layout: {
                        padding: {
                            bottom: 20
                        }
                    }
                }
            });

            // Initialize DataTable with proper pagination
            $('#kategoriTable').DataTable({
                "paging": true,
                "pageLength": 5,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "destroy": true,
                "language": {
                    "paginate": {
                        "previous": "← Sebelumnya",
                        "next": "Selanjutnya →"
                    },
                    "zeroRecords": "Tidak ada data yang tersedia",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                    "infoFiltered": "(disaring dari _MAX_ total data)",
                    "search": "Cari:",
                    "emptyTable": "Tidak ada data yang tersedia dalam tabel"
                },
                "columnDefs": [
                    { 
                        "orderable": false, 
                        "targets": 0 
                    }
                ],
                "order": [[ 1, "asc" ]],
                "drawCallback": function(settings) {
                    // Callback setelah tabel digambar
                    console.log('DataTable initialized with ' + settings.fnRecordsTotal() + ' records');
                }
            });
        });
    </script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <style>
        .dataTables_wrapper {
            margin-top: 20px;
        }
        
        .dataTables_wrapper .dataTables_filter {
            float: right;
            margin-bottom: 15px;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 4px;
            padding: 6px 12px;
            border: 1px solid #ddd;
            margin-left: 10px;
        }
        
        .dataTables_wrapper .dataTables_paginate {
            float: right;
            margin-top: 15px;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 6px 12px;
            margin: 0 2px;
            border-radius: 4px;
            border: 1px solid #ddd;
            background: #fff;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f8f9fa;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #007bff;
            color: white !important;
            border-color: #007bff;
        }
        
        .dataTables_wrapper .dataTables_info {
            padding-top: 8px;
            font-size: 0.875rem;
            float: left;
            margin-top: 15px;
        }
        
        .dataTables_wrapper .dataTables_length {
            display: none;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background-color: #343a40;
            color: white;
            font-weight: 600;
            text-align: center;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.05);
        }
        
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }
        
        /* Pastikan tabel tidak overflow */
        .table-responsive {
            overflow-x: auto;
        }
        
        /* Custom pagination styling */
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
@endsection