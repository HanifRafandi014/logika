@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.orang_tua')
@endsection

@section('content')
<style>
    /* CSS untuk membuat tabel lebih rapat dan pas dalam satu halaman */
    .table-responsive {
        height: auto; /* Biarkan tinggi responsif */
        overflow-y: hidden; /* Sembunyikan scroll vertikal */
    }

    .card-header h4 {
        font-size: 1.15rem; /* Mengecilkan ukuran judul card header */
        margin-bottom: 0;
    }

    /* Ukuran tombol Export Excel yang lebih kecil */
    .btn-export-sm {
        padding: 0.3rem 0.6rem; /* Padding lebih kecil */
        font-size: 0.8rem; /* Ukuran font lebih kecil */
        line-height: 1.5; /* Line height disesuaikan */
    }
    .btn-export-sm .fas {
        font-size: 0.75rem; /* Ukuran ikon di tombol juga lebih kecil */
        margin-right: 4px; /* Sedikit jarak antara ikon dan teks */
    }

    /* CSS for smaller "Kembali" button */
    .btn-sm-custom {
        padding: 0.3rem 0.6rem; /* Smaller padding */
        font-size: 0.8rem; /* Smaller font size */
        line-height: 1.5; /* Adjust line height */
    }

    .table.table-bordered th,
    .table.table-bordered td {
        padding: 3px 6px; /* Mengurangi padding vertikal dan horizontal lagi */
        vertical-align: middle;
        font-size: 0.75rem; /* Mengurangi ukuran font lagi untuk kerapatan maksimum */
    }

    .table-bordered thead th {
        vertical-align: middle;
        font-size: 0.8rem; /* Ukuran font header tabel sedikit lebih kecil */
    }

    /* Ukuran icon mata yang lebih kecil */
    .btn-icon-sm {
        font-size: 0.7rem; /* Ukuran font yang lebih kecil untuk ikon */
        padding: 3px 6px; /* Padding disesuaikan agar tombol tidak terlalu besar */
        line-height: 1; /* Pastikan ikon di tengah vertikal */
    }
    .btn-icon-sm .fas {
        vertical-align: middle; /* Memastikan ikon di tengah jika ada teks */
    }

    /* Penyesuaian untuk modal (optional, jika ingin lebih compact juga) */
    .modal-header .btn-close-white {
        filter: invert(1) grayscale(100%) brightness(200%); /* Membuat ikon X putih */
    }
</style>

<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white text-center py-3">
            {{-- Mengubah teks judul --}}
            <h4 class="mb-0">Ringkasan Transaksi Tahunan</h4>
        </div>
        <div class="card-body">
            {{-- Mengubah d-flex menjadi justify-content-between untuk menempatkan tombol di kiri dan kanan --}}
            <div class="d-flex justify-content-between mb-3">
                {{-- Tombol Kembali di sisi kiri --}}
                <a href="{{ route('orang_tua.dashboard') }}" class="btn btn-secondary btn-sm-custom">Kembali</a>

                {{-- Menggunakan kelas baru untuk tombol Export Excel di sisi kanan --}}
                <a href="{{ route('orang_tua.export_riwayat_transaksi_besar') }}" class="btn btn-success btn-export-sm">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Bulan</th>
                            <th>Saldo Awal</th>
                            <th>Pengeluaran</th>
                            <th>Saldo Akhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($riwayat as $item)
                            <tr>
                                <td>{{ $item['bulan'] }}</td>
                                <td class="text-end">
                                    @if(is_numeric($item['saldo_awal']))
                                        Rp {{ number_format($item['saldo_awal'], 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(is_numeric($item['pengeluaran']))
                                        Rp {{ number_format($item['pengeluaran'], 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(is_numeric($item['saldo_akhir']))
                                        Rp {{ number_format($item['saldo_akhir'], 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(is_numeric($item['pengeluaran']) && $item['pengeluaran'] > 0)
                                        <button type="button" class="btn btn-info btn-sm btn-icon-sm" data-bs-toggle="modal" data-bs-target="#detailPengeluaranModal" data-bulan-num="{{ $item['bulan_num'] }}" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Menghapus div text-center mt-4 yang membungkus tombol Kembali --}}
        </div>
    </div>
</div>

<div class="modal fade" id="detailPengeluaranModal" tabindex="-1" aria-labelledby="detailPengeluaranModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="detailPengeluaranModalLabel">Detail Pengeluaran Bulan <span id="modalBulanNama"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="pengeluaranDetails">
                    <p class="text-center text-muted">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var detailPengeluaranModal = document.getElementById('detailPengeluaranModal');
        detailPengeluaranModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var bulanNum = button.getAttribute('data-bulan-num'); // Extract month number from data-bulan-num attribute

            // Set the month name in the modal title
            var bulanNamaMap = {
                1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
                5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
                9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
            };
            document.getElementById('modalBulanNama').textContent = bulanNamaMap[bulanNum];

            // Fetch details via AJAX
            var detailsContainer = document.getElementById('pengeluaranDetails');
            detailsContainer.innerHTML = '<p class="text-center text-muted">Memuat data...</p>'; // Show loading

            fetch(`/orang_tua/pengurus_besar/get-detail-pengeluaran/${bulanNum}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.length > 0) {
                        let html = `<ul class="list-group">`;
                        data.forEach(item => {
                            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Kategori:</strong> ${item.kategori || '-'} <br>
                                                <strong>Tanggal:</strong> ${new Date(item.tanggal_transaksi).toLocaleDateString('id-ID')}
                                            </div>
                                            <span class="badge bg-danger rounded-pill">Rp ${new Intl.NumberFormat('id-ID').format(item.jumlah)}</span>
                                        </li>`;
                        });
                        html += `</ul>`;
                        detailsContainer.innerHTML = html;
                    } else {
                        detailsContainer.innerHTML = '<p class="text-center text-muted">Tidak ada pengeluaran untuk bulan ini.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching pengeluaran details:', error);
                    detailsContainer.innerHTML = '<p class="text-center text-danger">Gagal memuat detail pengeluaran.</p>';
                });
        });
    });
</script>
@endpush