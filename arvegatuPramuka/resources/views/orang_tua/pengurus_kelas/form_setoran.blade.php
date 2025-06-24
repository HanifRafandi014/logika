@extends('layouts.main') {{-- Sesuaikan dengan layout Anda --}}

@section('sidebar')
    @include('layouts.sidebar.orang_tua') {{-- Sesuaikan dengan sidebar pengurus kelas --}}
@endsection

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Form Setoran Iuran Pramuka ke Paguyuban Besar</h4>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('orang_tua.pengurus_kelas.proses_setoran') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="kelas_siswa_display" class="form-label">Kelas Asal Iuran</label>
                    <input type="text" class="form-control" id="kelas_siswa_display" value="{{ $kelasSiswa }}" readonly>
                    <small class="form-text text-muted">Setoran ini untuk iuran dari kelas {{ $kelasSiswa }}.</small>
                </div>

                <div class="mb-3">
                    <label for="jumlah_siswa" class="form-label">Jumlah Siswa di Kelas {{ $kelasSiswa }}</label>
                    <input type="number" class="form-control" id="jumlah_siswa" name="jumlah_siswa" value="{{ old('jumlah_siswa', $jumlahSiswaKelas) }}" min="1" required>
                    <small class="form-text text-muted">Pastikan jumlah siswa sesuai dengan kondisi terkini.</small>
                </div>

                <div class="mb-3">
                    <label for="bulan_setor" class="form-label">Bulan & Tahun Iuran yang Disetor</label>
                    <input type="month" class="form-control" id="bulan_setor" name="bulan_setor" value="{{ old('bulan_setor', \Carbon\Carbon::now()->format('Y-m')) }}" required>
                    <small class="form-text text-muted">Pilih bulan dan tahun iuran yang akan disetorkan (60% bagian pramuka).</small>
                </div>

                <div class="mb-3">
                    <label for="jumlah_setoran_pramuka" class="form-label">Perkiraan Jumlah Setoran ke Paguyuban Besar (60%)</label>
                    <input type="text" class="form-control" id="jumlah_setoran_pramuka" value="Rp 0" readonly>
                    <small class="form-text text-muted">Ini adalah 60% dari total iuran (jumlah siswa x Rp 60.000).</small>
                </div>

                <div class="mb-3">
                    <label for="bukti_setor" class="form-label">Bukti Setoran (Opsional)</label>
                    <input type="file" class="form-control" id="bukti_setor" name="bukti_setor" accept="image/*">
                    <small class="form-text text-muted">Unggah foto bukti transfer/setoran.</small>
                </div>

                <h5 class="mt-4">Informasi Penerima Setoran (Pengurus Paguyuban Besar)</h5>
                <div class="mb-3">
                    <label class="form-label">Nama Pengurus Besar</label>
                    <input type="text" class="form-control" value="{{ $pengurusBesar->nama ?? 'Belum terdaftar' }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nomor HP Pengurus Besar</label>
                    <input type="text" class="form-control" value="{{ $pengurusBesar->no_hp ?? 'Belum terdaftar' }}" readonly>
                </div>

                <button type="submit" class="btn btn-primary">Proses Setoran</button>
                <a href="{{ route('orang_tua.pengurus_kelas.rekapan_pembayaran_kelas') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const jumlahSiswaInput = document.getElementById('jumlah_siswa');
        const jumlahSetoranPramukaInput = document.getElementById('jumlah_setoran_pramuka');

        function updateJumlahSetoran() {
            const jumlahSiswa = parseInt(jumlahSiswaInput.value);
            if (!isNaN(jumlahSiswa) && jumlahSiswa > 0) {
                const totalSetoranPramuka = jumlahSiswa * 60000;
                jumlahSetoranPramukaInput.value = 'Rp ' + totalSetoranPramuka.toLocaleString('id-ID');
            } else {
                jumlahSetoranPramukaInput.value = 'Rp 0';
            }
        }

        jumlahSiswaInput.addEventListener('input', updateJumlahSetoran);

        // Initial update
        updateJumlahSetoran();
    });
</script>
@endpush
@endsection