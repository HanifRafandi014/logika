<!-- Sidebar -->
{{-- <style>
.sidebar .nav-item {
  background-color: #1E2A38; /* warna dasar */
  border-radius: 8px;
  margin: 4px 8px;
  transition: all 0.3s ease;
}

/* Hanya nav-item.active yang putih */
.sidebar .nav-item.active {
  background-color: #ffffff;
  box-shadow: inset 0 0 0 2px #007bff;
}

.sidebar .nav-item a {
  color: white;
  display: flex;
  align-items: center;
  padding: 10px 15px;
  border-radius: 8px;
  text-decoration: none;
}

.sidebar .nav-item.active a {
  color: black;
  font-weight: bold;
}

/* Hover hanya ubah background jika tidak aktif */
.sidebar .nav-item:not(.active):hover {
  background-color: #343a40;
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
          <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a
              class="nav-link"
              href="{{route('admin.dashboard')}}"
              class="collapsed"
              aria-expanded="false"
            >
              <i class="fas fa-home"></i>
              <span>Dashboard</span>
              {{-- <span class="caret"></span> --}}
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#dataMenu" aria-expanded="false" aria-controls="dataMenu">
                <i class="fas fa-layer-group"></i>
                <span>Data User</span>
                <span class="caret"></span> </a>
            <div class="collapse" id="dataMenu">
                <ul class="nav nav-collapse">
                    <li>
                        <a href="{{route('data-guru.index')}}"> <span class="sub-item">Data Guru</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('data-pembina.index')}}"> <span class="sub-item">Data Pembina</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('data-siswa.index')}}"> <span class="sub-item">Data Siswa</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('data-alumni.index')}}"> <span class="sub-item">Data Alumni</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('data-orang-tua.index')}}"> <span class="sub-item">Data Orang Tua</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
          <li class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#skuSkkMenu" aria-expanded="false" aria-controls="skuSkkMenu">
                <i class="fas fa-layer-group"></i>
                <span>SKU SKK</span>
                <span class="caret"></span> </a>
            <div class="collapse" id="skuSkkMenu">
                <ul class="nav nav-collapse">
                    <li>
                        <a href="{{route('manajemen_sku.index')}}"> <span class="sub-item">Data SKU</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('manajemen_skk.index')}}"> <span class="sub-item">Data SKK</span>
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