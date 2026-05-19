<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'e-Office') }}</title>

    <link rel="manifest" href="/manifest.json">
       <!-- Custom fonts for this template-->
    <link href="{{ asset('sb-admin-2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
   
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('sb-admin-2/css/sb-admin-2.min.css')}}" rel="stylesheet">
    <link href="{{ asset('sb-admin-2/css/app.css') }}" rel="stylesheet">
    
    @push('styles')
{{-- <style>
    @media print {
        .btn, .sidebar, .navbar, .footer { display: none !important; }
    }
</style> --}}
@endpush
</head>
<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        {{-- <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar"> --}}
            <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion toggled" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">E-Office</div>
            </a>

            <!-- Divider -->
            

            <!-- Nav Item - Dashboard -->
            <!-- Nav Item - Pages Collapse Menu -->
            <div class="sidebar-heading">
                Interface
            </div>
            <hr class="sidebar-divider my-0">
            <li class="nav-item">
                <a class="nav-link collapsed" href="/home">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <hr class="sidebar-divider my-0">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person-circle"></i>
                    <span>Profil Saya</span>
                </a>
            </li>
            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.cabangs') }}">
                    <i class="bi bi-person-circle"></i>
                    <span>Master Unit Kerja</span>
                </a>
            </li>
            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.jabatans') }}">
                    <i class="bi bi-person-circle"></i>
                    <span>Master Jabatan</span>
                </a>
            </li>
            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                   <a class="nav-link" href="{{ route('letters.create') }}">
                       <i class="fas fa-fw fa-tachometer-alt"></i>
                       <span>Buat Surat</span></a>
                       <a class="nav-link" 
                       </li> 
                  </a>
               
            <hr class="sidebar-divider my-0">
            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
        aria-expanded="true" aria-controls="collapseTwo">
        <i class="fas fa-fw fa-cog"></i>
        <span>Data Surat</span>
    </a>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Jenis Surat:</h6>
            
            <!-- ✅ SEMUA SURAT -->
            <a class="collapse-item {{ request()->routeIs('letters.index') ? 'active' : '' }}" 
               href="{{ route('letters.index') }}">
                <i class="fas fa-fw fa-files me-1"></i>Semua Surat
            </a>
            
            <!-- ✅ SURAT MASUK -->
            <a class="collapse-item {{ request()->routeIs('letters.masuk') ? 'active' : '' }}" 
               href="{{ route('letters.masuk') }}">
                <i class="fas fa-fw fa-inbox me-1"></i>Surat Masuk
            </a>
            
            <!-- ✅ SURAT KELUAR (FIX: tambah route) -->
            <a class="collapse-item {{ request()->routeIs('letters.keluar') ? 'active' : '' }}" 
               href="{{ route('letters.keluar') }}">
                <i class="fas fa-fw fa-paper-plane me-1"></i>Surat Keluar
            </a>
            
            <!-- ✅ NOTA DINAS (BARU) -->
            <a class="collapse-item {{ request()->routeIs('letters.nota') ? 'active' : '' }}" 
               href="{{ route('letters.nota') }}">
                <i class="fas fa-fw fa-sticky-note me-1"></i>Nota Dinas
            </a>
            
        </div>
    </div>
</li>
            <hr class="sidebar-divider my-0">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('disposisi.inbox') }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Disposisi/inbox</span>
                </a>
            </li>
            

            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                   <a class="nav-link" href="{{ route('reports.index') }}">
                       <i class="fas fa-fw fa-tachometer-alt"></i>
                       <span>Laporan</span></a>
                       <a class="nav-link" 
                       </i> 
                  </a>
               </li>   
               
               
               <!-- Di sidebar atau navbar -->
{{-- <li class="nav-item">
    <a class="nav-link" href="{{ route('reports.index') }}">
        <i class="fas fa-chart-bar me-1"></i>
        <span>Laporan</span>
    </a>
</li>
            <!-- Divider --> --}}
            <hr class="sidebar-divider">
            <!-- Heading -->
            <div class="sidebar-heading">Management User</div>

 <hr class="sidebar-divider my-0">
            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                   <a class="nav-link" href="{{ route('users.index') }}">
                       <i class="fas fa-fw fa-tachometer-alt"></i>
                       <span>Users</span></a>
                       <a class="nav-link" 
                       </i> 
                  </a>
               </li>   
            <!-- Nav Item - Management User -->
            <li class="nav-item active">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages" 
                aria-expanded="false" aria-controls="collapsePages">
                    <!-- Ikon dan Teks Label (Penting agar teks muncul seperti di gambar) -->
                   
                    <span><i class="fas fa-fw fa-tachometer-alt"></i></span>
                    
                </a>
                
                <!-- ID collapsePages tanpa class 'show' agar tertutup secara default -->
                <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        @guest
                            <!-- Menu untuk Guest -->
                            <a class="collapse-item" href="{{ route('login') }}">Login</a>
                            <a class="collapse-item" href="{{ route('register') }}">Register</a>
                        @else
                            <!-- Menu untuk Authenticated User -->
                            <h6 class="collapse-header">User Menu:</h6>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="collapse-item btn btn-link text-danger" 
                                        style="text-decoration: none; width: 100%; text-align: left; border: none; padding: 0.5rem 1rem;">
                                    <span>Logout</span>
                                </button>
                            </form>
                        @endguest
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    {{-- <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button> --}}
                    
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i> <!-- Pastikan baris ini ada -->
                    </button>

                    <!-- Topbar Search -->
                    {{-- <form
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form> --}}

                    <!-- Topbar Navbar -->
                    @include('layouts.part.alert')
                    
                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        @include('layouts.part.navbar')
                        

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
               <div class="container-fluid mt-4">

                    <!-- Page Heading -->
                    
                    @yield('content')

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->
 </div>
 
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            @include('layouts.part.footer')
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
     @include('layouts.part.scripts')
<!-- Simpan tepat sebelum </body> -->
{{-- <script>
  if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost')) {
    navigator.serviceWorker.register('/sw.js')
      .then(reg => console.log('✅ SW aktif:', reg.scope))
      .catch(err => console.error('❌ SW gagal:', err));
  }
</script> --}}
<script>
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
      .then(reg => console.log('✅ SW terdaftar:', reg.scope))
      .catch(err => console.error('❌ Gagal daftar SW:', err));
  }
</script>

</body>
</html>
