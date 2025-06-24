<!-- Sidebar -->
{{-- <style>
  .sidebar .nav-item {
    background-color: #1E2A38; /* biru navy agak muda */
    border-radius: 8px;
    margin: 4px 8px;
    transition: all 0.3s ease;
    color: white;
  }
  
  /* Hover effect */
  .sidebar .nav-item:hover {
    background-color: #ffffff;
    color: #000000;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
  }
  
  /* Link di dalam nav-item */
  .sidebar .nav-item a {
    color: #fff;
    display: flex;
    align-items: center;
    padding: 10px 15px;
    border-radius: 8px;
  }
  
  /* Active nav-item */
  .sidebar .nav-item.active {
    background-color: #ffffff;
    box-shadow: inset 0 0 0 2px #007bff; /* garis pinggir */
  }
  
  .sidebar .nav-item.active a {
    color: black;
    font-weight: bold;
  }
  </style> --}}

<div class="sidebar">
    <div class="sidebar-logo">
      <!-- Logo Header -->
      <div class="logo-header" data-background-color="dark">
        <a href="index.html" class="logo">
          <img
            src="{{ asset('assets/img/kaiadmin/logo_light.svg') }}"
            alt="navbar brand"
            class="navbar-brand"
            height="20"
          />
        </a>
        <div class="nav-toggle">
          <button class="btn btn-toggle toggle-sidebar">
            <i class="gg-menu-right"></i>
          </button>
          <button class="btn btn-toggle sidenav-toggler">
            <i class="gg-menu-left"></i>
          </button>
        </div>
        <button class="topbar-toggler more">
          <i class="gg-more-vertical-alt"></i>
        </button>
      </div>
      <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
      <div class="sidebar-content">
        <ul class="nav nav-secondary">
          <li class="nav-item">
            <a
              class="nav-link"
              href="{{route('orang_tua.dashboard')}}"
              class="collapsed"
              aria-expanded="false"
            >
              <i class="fas fa-home"></i>
              <span>Dashboard</span>
              {{-- <span class="caret"></span> --}}
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('orang-tua.profile.form') }}">
              <i class="fas fa-layer-group"></i>
              <span>Data Orang Tua</span>
            </a>
          </li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#nilaiSiswaMenu" aria-expanded="false" aria-controls="nilaiSiswaMenu">
                <i class="fas fa-layer-group"></i>
                <span>Nilai Siswa</span>
                <span class="caret"></span> </a>
            <div class="collapse" id="nilaiSiswaMenu">
                <ul class="nav nav-collapse">
                    <li>
                        <a href="{{ route('orang_tua.lihat_nilai_akademik') }}"> <span class="sub-item">Nilai Akademik</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('orang_tua.lihat_nilai_non_akademik') }}"> <span class="sub-item">Nilai Non Akademik</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#keuanganMenu" aria-expanded="false" aria-controls="keuanganMenu">
                <i class="fas fa-layer-group"></i>
                <span>Keuangan</span>
                <span class="caret"></span>
            </a>
            <div class="collapse" id="keuanganMenu">
                <ul class="nav nav-collapse">
                    <li>
                        <a href="{{ route('pembayaran-iuran.index') }}">
                            <span class="sub-item">Pembayaran Iuran</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('orang_tua.pembayaran-iuran.riwayat') }}">
                            <span class="sub-item">Riwayat Pembayaran</span>
                        </a>
                    </li>

                    {{-- Bagian untuk Pengurus Paguyuban Kelas --}}
                    @if(Auth::check() && Auth::user()->role === 'orang_tua' && Auth::user()->orang_tua && Auth::user()->orang_tua->status === 'Pengurus Paguyuban Kelas')
                        <li class="nav-item">
                            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#paguyubanKelasMenu" aria-expanded="false" aria-controls="paguyubanKelasMenu">
                                <i class="fas fa-university"></i>
                                <span>Paguyuban Kelas</span>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse" id="paguyubanKelasMenu">
                                <ul class="nav nav-collapse">
                                    <li>
                                        <a href="{{ route('orang_tua.pengurus_kelas.rekapan_setoran') }}">
                                            <span class="sub-item">Rekapan Pembayaran Kelas</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('orang_tua.pengurus_kelas.riwayat_pembayaran_kelas') }}">
                                            <span class="sub-item">Riwayat Pembayaran Kelas</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('orang_tua.pengurus_kelas.form_setoran') }}">
                                            <span class="sub-item">Form Setoran Pramuka</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- Bagian untuk Pengurus Paguyuban Besar --}}
                    @if(Auth::check() && Auth::user()->role === 'orang_tua' && Auth::user()->orang_tua && Auth::user()->orang_tua->status === 'Pengurus Paguyuban Besar')
                        <li class="nav-item">
                            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#paguyubanBesarMenu" aria-expanded="false" aria-controls="paguyubanBesarMenu">
                                <i class="fas fa-university"></i> {{-- Contoh ikon untuk paguyuban besar --}}
                                <span>Paguyuban Besar</span>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse" id="paguyubanBesarMenu">
                                <ul class="nav nav-collapse">
                                    <li>
                                        <a href="{{ route('orang_tua.pengurus_besar.rekapan_setoran_kelas') }}">
                                            <span class="sub-item">Rekapan Setoran Kelas</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('orang_tua.pengurus_besar.manajemen_keuangan') }}">
                                            <span class="sub-item">Manajemen Keuangan</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('orang_tua.pengurus_besar.riwayat_transaksi_keuangan') }}">
                                            <span class="sub-item">Riwayat Transaksi Keuangan</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </li>
        </ul>
      </div>
    </div>
  </div>
  <!-- End Sidebar -->