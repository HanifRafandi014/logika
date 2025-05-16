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
              href="{{route('orang-tua.dashboard')}}"
              class="collapsed"
              aria-expanded="false"
            >
              <i class="fas fa-home"></i>
              <span>Dashboard</span>
              {{-- <span class="caret"></span> --}}
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#base">
              <i class="fas fa-layer-group"></i>
              <span>Data Orang Tua</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#base">
              <i class="fas fa-layer-group"></i>
              <span>Pembayaran Iuran</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <!-- End Sidebar -->