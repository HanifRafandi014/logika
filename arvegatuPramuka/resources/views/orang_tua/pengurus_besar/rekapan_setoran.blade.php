@extends('layouts.main') {{-- Sesuaikan dengan layout Anda --}}

@section('sidebar')
    @include('layouts.sidebar.orang_tua') {{-- Sesuaikan dengan sidebar pengurus besar --}}
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Rekapan Seluruh Setoran Iuran Pramuka dari Kelas</h4>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table id="setoranTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kelas Asal</th>
                            <th>Bulan Iuran Disetor</th>
                            <th>Pengurus Kelas</th>
                            <th>Siswa Pengurus</th>
                            <th>Jumlah Setoran</th>
                            <th>Bukti Setor</th>
                            <th>Status Verifikasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($setoranDariKelas as $setoran)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $setoran->kelas }}</td>
                                <td>{{ \Carbon\Carbon::parse($setoran->bulan_setor)->translatedFormat('F Y') }}</td>
                                <td>{{ $setoran->pengurus_kelas->nama ?? 'N/A' }}</td>
                                <td>{{ $setoran->pengurus_kelas->siswa->nama ?? 'N/A' }}</td>
                                <td>Rp {{ number_format($setoran->jumlah, 0, ',', '.') }}</td>
                                <td>
                                    @if ($setoran->bukti_setor)
                                        <a href="{{ Storage::url($setoran->bukti_setor) }}" target="_blank">Lihat Bukti</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($setoran->status_verifikasi)
                                        <span class="badge bg-success">Terverifikasi</span>
                                    @else
                                        <span class="badge bg-danger">Belum</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm {{ $setoran->status_verifikasi ? 'btn-secondary' : 'btn-success' }} toggle-verifikasi-btn"
                                            data-id="{{ $setoran->id }}"
                                            data-current-status="{{ $setoran->status_verifikasi ? '1' : '0' }}"
                                            {{ $setoran->status_verifikasi ? 'disabled' : '' }}>
                                        {{ $setoran->status_verifikasi ? 'Sudah Verifikasi' : 'Verifikasi' }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Belum ada setoran dari kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#setoranTable').DataTable({
            // Konfigurasi DataTables Anda
            pageLength: 10,
            searching: true
        });

        // Event listener untuk tombol verifikasi
        $('.toggle-verifikasi-btn').on('click', function() {
            const button = $(this);
            const setoranId = button.data('id');
            const currentStatus = button.data('current-status');
            const newStatus = currentStatus === 0 ? 1 : 0; // Toggle status (false to true, true to false)

            if (confirm('Yakin ingin ' + (newStatus === 1 ? 'memverifikasi' : 'membatalkan verifikasi') + ' setoran ini?')) {
                $.ajax({
                    url: `/orang-tua/pengurus-besar/setoran/${setoranId}/verify`,
                    type: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status_verifikasi: newStatus
                    },
                    success: function(response) {
                        alert(response.success);
                        // Perbarui tampilan tombol dan badge secara dinamis
                        if (response.newStatus) {
                            button.removeClass('btn-success').addClass('btn-secondary').text('Sudah Verifikasi').prop('disabled', true);
                            button.closest('tr').find('.badge').removeClass('bg-danger').addClass('bg-success').text('Terverifikasi');
                        } else {
                            button.removeClass('btn-secondary').addClass('btn-success').text('Verifikasi').prop('disabled', false);
                            button.closest('tr').find('.badge').removeClass('bg-success').addClass('bg-danger').text('Belum');
                        }
                        button.data('current-status', response.newStatus ? 1 : 0);
                    },
                    error: function(xhr) {
                        alert('Gagal memperbarui status: ' + (xhr.responseJSON.error || 'Terjadi kesalahan.'));
                    }
                });
            }
        });
    });
</script>
@endpush
@endsection