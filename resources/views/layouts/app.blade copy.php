<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    
    <title>{{ config('app.name', 'E-Office PDAM') }}</title>
    
    <!-- Custom fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles for SB Admin 2 -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    
    {{-- Custom CSS untuk Mobile --}}
    @push('styles')
    <style>
        /* Fix sidebar overflow di mobile */
        @media (max-width: 768px) {
            .sidebar {
                width: 0 !important;
                overflow: hidden;
                transition: all 0.3s;
            }
            
            .sidebar.toggled {
                width: 16rem !important;
            }
            
            .sidebar .nav-item {
                margin: 0;
            }
            
            /* Logo tidak overflow */
            .sidebar-brand {
                padding: 1rem;
                overflow: hidden;
                white-space: nowrap;
            }
            
            .sidebar-brand-text {
                font-size: 0.9rem;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            /* Content wrapper menyesuaikan */
            #wrapper.toggled ~ #content-wrapper {
                margin-left: 0;
            }
            
            /* Toggle button selalu terlihat di mobile */
            .topbar .navbar-nav .nav-item.dropdown .dropdown-toggle::after {
                display: none;
            }
        }
        
        /* Desktop: sidebar selalu terlihat */
        @media (min-width: 769px) {
            .sidebar {
                width: 14rem !important;
            }
        }
    </style>
    @endpush
</head>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav sidebar sidebar-dark accordion bg-gradient-primary" id="accordionSidebar">
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/') }}">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">
                    SB Adllmin 2<sup>2</sup>
                </div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item {{ request()->is('home*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('home') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Nav Item - Buat Surat -->
            <li class="nav-item {{ request()->is('letters/create*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('letters.create') }}">
                    <i class="fas fa-fw fa-envelope"></i>
                    <span>Buat Surat</span>
                </a>
            </li>

            <!-- Nav Item - Profil -->
            <li class="nav-item {{ request()->is('profile*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('profile.edit') }}">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Profil Saya</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Interface
            </div>

            <!-- Data Berkas Submenu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDataBerkas">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Data Berkas</span>
                </a>
                <div id="collapseDataBerkas" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="{{ route('letters.index') }}">Semua Berkas</a>
                        <a class="collapse-item" href="{{ route('letters.masuk') }}">Surat Masuk</a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Addons
            </div>

            <!-- Pages -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages">
                    <i class="fas fa-fw fa-folder"></i>
                    <span>Pages</span>
                </a>
                <div id="collapsePages" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Login Screens:</h6>
                        @guest
                            <a class="collapse-item" href="{{ route('login') }}">Login</a>
                            <a class="collapse-item" href="{{ route('register') }}">Register</a>
                        @else
                            <a class="collapse-item" href="{{ route('home') }}">Dashboard</a>
                        @endguest
                    </div>
                </div>
            </li>

            <!-- Disposisi/Inbox -->
            <li class="nav-item {{ request()->is('disposisi*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('disposisi.inbox') }}">
                    <i class="fas fa-fw fa-inbox"></i>
                    <span>Disposisi/Inbox</span>
                </a>
            </li>

            <!-- Laporan -->
            <li class="nav-item {{ request()->is('tables*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('tables') }}">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Laporan</span>
                </a>
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
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - User Information -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">
                                    <i class="fas fa-fw fa-sign-in-alt"></i>
                                    <span class="mr-2 d-none d-lg-inline text-gray-600">Login</span>
                                </a>
                            </li>
                        @else
                            <li class="nav-item dropdown no-arrow">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                    <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                        {{ Auth::user()->nama_lengkap ?? Auth::user()->username }}
                                    </span>
                                    <span class="badge badge-primary">{{ Auth::user()->level }}</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Profile
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Settings
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Activity Log
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('logout') }}" 
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Logout
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; E-Office PDAM {{ date('Y') }}</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form-modal').submit();">
                        Logout
                    </a>
                    <form id="logout-form-modal" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

    {{-- Custom JavaScript untuk Mobile Toggle --}}
    @stack('scripts')
    <script>
        // Toggle sidebar di mobile
        $(document).ready(function() {
            $('#sidebarToggleTop').on('click', function(e) {
                e.preventDefault();
                $('body').toggleClass('sidebar-toggled');
                $('.sidebar').toggleClass('toggled');
                
                // Prevent body scroll when sidebar is open
                if ($('.sidebar').hasClass('toggled')) {
                    $('body').css('overflow', 'hidden');
                } else {
                    $('body').css('overflow', 'auto');
                }
            });

            // Close sidebar when clicking outside on mobile
            $(document).on('click', function(e) {
                if ($(window).width() < 768 && $('.sidebar').hasClass('toggled')) {
                    if (!$(e.target).closest('.sidebar, #sidebarToggleTop').length) {
                        $('body').removeClass('sidebar-toggled');
                        $('.sidebar').removeClass('toggled');
                        $('body').css('overflow', 'auto');
                    }
                }
            });

            // Close sidebar when clicking nav link on mobile
            $(window).on('resize', function() {
                if ($(window).width() >= 768) {
                    $('body').removeClass('sidebar-toggled');
                    $('.sidebar').removeClass('toggled');
                    $('body').css('overflow', 'auto');
                }
            });
        });
    </script>
</body>