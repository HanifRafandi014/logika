
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
              href="{{route('pembina.dashboard')}}"
              class="collapsed"
              aria-expanded="false"
            >
              <i class="fas fa-home"></i>
              <span>Dashboard</span>
              {{-- <span class="caret"></span> --}}
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{route('pembina.profil')}}">
              <i class="fas fa-layer-group"></i>
              <span>Data Pembina</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#nilaiMenu" aria-expanded="false" aria-controls="nilaiMenu">
                <i class="fas fa-layer-group"></i>
                <span>Nilai Siswa</span>
                <span class="caret"></span> </a>
            <div class="collapse" id="nilaiMenu">
                <ul class="nav nav-collapse">
                    <li>
                        <a href="{{route('nilai_non_akademik.index')}}"> <span class="sub-item">Nilai Non Akademik</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('lihat_nilai.nilai_akademik')}}"> <span class="sub-item">Lihat Nilai Akademik</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('lihat_nilai.nilai_non_akademik')}}"> <span class="sub-item">Lihat Nilai Non Akademik</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#skuSkkMenu" aria-expanded="false" aria-controls="skuSkkMenu">
                <i class="fas fa-layer-group"></i>
                <span>Penilaian Pramuka</span>
                <span class="caret"></span> </a>
            <div class="collapse" id="skuSkkMenu">
                <ul class="nav nav-collapse">
                    <li>
                        <a href="{{ route('nilai_sku.index') }}"> <span class="sub-item">Penilaian SKU</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pencapaian-sku.index') }}"> <span class="sub-item">Pencapaian Nilai SKU</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('nilai_skk.index') }}"> <span class="sub-item">Penilaian SKK</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pencapaian-skk.index') }}"> <span class="sub-item">Pencapaian Nilai SKK</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#lombaMenu" aria-expanded="false" aria-controls="lombaMenu">
                <i class="fas fa-layer-group"></i>
                <span>Clustering Regu Inti</span>
                <span class="caret"></span> </a>
            <div class="collapse" id="lombaMenu">
                <ul class="nav nav-collapse">
                    <li>
                        <a href="{{ route('lomba.index') }}"> <span class="sub-item">Kompetensi Lomba</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('pembina.rekomendasi.index')}}"> <span class="sub-item">Hasil Clustering</span>
                        </a>
                    </li>
                    <li>
                        <a href="#"> <span class="sub-item">Grafik Hasil Clustering</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        </ul>
      </div>
    </div>
  </div>
  <!-- End Sidebar -->