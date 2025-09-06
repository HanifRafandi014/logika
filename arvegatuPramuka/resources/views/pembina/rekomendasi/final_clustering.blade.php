@extends('layouts.main')

@section('sidebar')
    @include('layouts.sidebar.pembina')
@endsection

@section('content')
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>

<div class="container mt-4">
    <h4 class="mb-3">Final Clustering Rekomendasi</h4>

    @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Data Hasil Clustering</h5>
            <small class="text-muted">Pilih siswa yang akan disimpan ke tabel <code>clustering_finals</code></small>
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <!-- Filter di kiri -->
                <div class="col-md-3">
                    <select class="form-select" id="filterGender">
                        <option value="">Semua Jenis Kelamin</option>
                        <option value="1" {{ $selectedGender == '1' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="0" {{ $selectedGender == '0' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <!-- Total data di tengah -->
                <div class="text-center">
                    @if($hasilClusterings->count() > 0)
                        <small class="text-muted">
                            Total data: <strong>{{ $hasilClusterings->count() }}</strong> siswa
                        </small>
                    @endif
                </div>

                <!-- Tombol Export -->
                <div class="d-flex gap-2">
                    <a href="{{ route('pembina.rekomendasi.export_final_clustering', ['gender' => $selectedGender]) }}"
                        class="btn btn-success" title="Export Excel">
                        <i class="fas fa-file-excel"></i>
                    </a>
                </div>
            </div>

            <!-- FORM Mulai -->
            <form action="{{ route('pembina.rekomendasi.save_final_clustering') }}" method="POST" id="clusteringForm">
                @csrf
                <input type="hidden" name="gender" value="{{ $selectedGender }}">

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkAll">
                        <label class="form-check-label" for="checkAll">
                            <strong>Pilih Semua</strong>
                        </label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="display table table-striped table-hover" id="clusteringTable">
                        <thead class="table-primary">
                            <tr>
                                <th><i class="fas fa-check-square"></i></th>
                                <th>Nama Siswa</th>
                                <th>Jenis Kelamin</th>
                                <th>Kategori Lomba</th>
                                <th>Rata-rata Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hasilClusterings as $hasil)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="row-checkbox" name="selected[]" value="{{ $hasil->siswa_id }}" {{ in_array($hasil->siswa_id, $selectedData) ? 'checked' : '' }}>
                                    </td>
                                    <td><strong>{{ $hasil->siswa->nama ?? '-' }}</strong></td>
                                    <td>
                                        <span class="badge bg-{{ ($hasil->siswa->jenis_kelamin ?? '') == 1 ? 'primary' : 'danger' }}">
                                            {{ ($hasil->siswa->jenis_kelamin ?? '') == 1 ? 'Laki-laki' : 'Perempuan' }}
                                        </span>
                                    </td>
                                    <td><span class="badge bg-info">{{ $hasil->kategori_lomba }}</span></td>
                                    <td><span class="badge bg-success">{{ number_format($hasil->rata_rata_skor, 2) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="fas fa-exclamation-triangle"></i> Tidak ada data hasil clustering
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($hasilClusterings->count() > 0)
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary" id="saveBtn" title="Simpan Clustering Final" disabled>
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#clusteringTable').DataTable({
            pageLength: 5
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const checkAllBtn = document.getElementById('checkAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const saveBtn = document.getElementById('saveBtn');
        const form = document.getElementById('clusteringForm');
        const filterGenderSelect = document.getElementById('filterGender');

        // Toggle semua checkbox
        checkAllBtn.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => { checkbox.checked = this.checked; });
            updateSaveButton();
        });

        // Toggle individu checkbox
        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const total = rowCheckboxes.length;
                const checked = document.querySelectorAll('.row-checkbox:checked').length;

                checkAllBtn.checked = (checked === total);
                checkAllBtn.indeterminate = (checked > 0 && checked < total);

                updateSaveButton();
            });
        });

        function updateSaveButton() {
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            if (!saveBtn) return;
            saveBtn.disabled = checked === 0;
            saveBtn.innerHTML = checked === 0
                ? '<i class="fas fa-save"></i> Simpan'
                : `<i class="fas fa-save"></i> Simpan ${checked} Data`;
        }

        filterGenderSelect.addEventListener('change', function() {
            const selectedGender = this.value;
            const currentUrl = new URL(window.location.href);
            if (selectedGender !== '') {
                currentUrl.searchParams.set('gender', selectedGender);
            } else {
                currentUrl.searchParams.delete('gender');
            }
            window.location.href = currentUrl.toString();
        });

        form.addEventListener('submit', function(e) {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            if (checkedCount === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal satu siswa untuk disimpan.');
                return false;
            }
            if (!confirm(`Yakin ingin menyimpan ${checkedCount} data ke tabel clustering_finals?`)) {
                e.preventDefault();
                return false;
            }
        });

        updateSaveButton();
    });
</script>
@endsection
