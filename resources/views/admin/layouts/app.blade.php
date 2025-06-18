<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'AKAR')</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Admin Custom CSS -->
    <style>
        :root {
            --primary-color: #bf6420;
            --secondary-color: #1cc88a;
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: #000000;
            background-image: none;
            transition: all 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar.collapsed {
            width: 70px;
        }
        
        .sidebar-heading {
            padding: 1rem;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #bf6420;
        }
        
        .sidebar-heading img {
            width: 24px;
            height: 24px;
            margin-right: 8px;
        }
        
        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin: 0.5rem 1rem;
        }
        
        .sidebar-item {
            padding: 0.5rem 1rem;
            margin: 0.2rem 0;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-item.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .sidebar-item i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }
        
        .sidebar.collapsed .sidebar-item span {
            display: none;
        }
        
        .sidebar.collapsed .sidebar-heading span {
            display: none;
        }
        
        /* Content Area */
        .content-wrapper {
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
        }
        
        .content-wrapper.expanded {
            margin-left: 70px;
        }
        
        /* Header */
        .header {
            background-color: #bf6420;
            height: var(--header-height);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            color: white;
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 900;
            transition: all 0.3s;
        }
        
        .content-wrapper.expanded .header {
            left: 70px;
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
        }
        
        .header-search {
            position: relative;
            width: 350px;
        }
        
        .header-search input {
            border-radius: 50px;
            padding-left: 2.5rem;
            background-color: #f8f9fc;
            border-color: #f8f9fc;
        }
        
        .header-search i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #d1d3e2;
        }
        
        .user-dropdown {
            color: white;
        }
        
        .user-dropdown .dropdown-toggle {
            color: white;
        }
        
        .user-dropdown img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
        }
        
        /* Main Content */
        .main-content {
            padding: 1.5rem;
            min-height: calc(100vh - var(--header-height));
            margin-top: var(--header-height);
        }
        
        /* Cards */
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            font-weight: bold;
        }
        
        /* Footer */
        .footer {
            padding: 1.5rem;
            background-color: white;
            border-top: 1px solid #e3e6f0;
            text-align: center;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar-item span {
                display: none;
            }
            
            .content-wrapper {
                margin-left: 70px;
            }
            
            .header-search {
                width: 200px;
            }
        }
    </style>
    
    <!-- Additional CSS -->
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-heading">
            <img src="{{ asset('favicon.ico') }}" alt="Logo">
            <span>AKAR</span>
        </div>
        <div class="sidebar-divider"></div>
        
        <a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        
        <!-- User Management -->
        <div class="sidebar-divider"></div>
        <div class="sidebar-item">
            <i class="bi bi-people"></i>
            <span>Manajemen User</span>
        </div>
        <a href="{{ route('admin.users.index') }}" class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-person"></i>
            <span>Users</span>
        </a>
        <a href="{{ route('admin.admins.index') }}" class="sidebar-item {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i>
            <span>Admins</span>
        </a>
        <a href="{{ route('admin.registrations.index') }}" class="sidebar-item {{ request()->routeIs('admin.registrations.*') ? 'active' : '' }}">
            <i class="bi bi-person-plus"></i>
            <span>Pendaftaran</span>
        </a>
        
        <!-- Content Management -->
        <div class="sidebar-divider"></div>
        <div class="sidebar-item">
            <i class="bi bi-clipboard-data"></i>
            <span>Data Checklist</span>
        </div>
        <a href="{{ route('admin.checklists.index') }}" class="sidebar-item {{ request()->routeIs('admin.checklists.*') ? 'active' : '' }}">
            <i class="bi bi-check-square"></i>
            <span>Checklists</span>
        </a>
        
        <!-- System -->
        <div class="sidebar-divider"></div>
        <div class="sidebar-item">
            <i class="bi bi-gear"></i>
            <span>Sistem</span>
        </div>
        <a href="{{ route('admin.settings.index') }}" class="sidebar-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="bi bi-sliders"></i>
            <span>Pengaturan</span>
        </a>
        <a href="{{ route('admin.logs.index') }}" class="sidebar-item {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i>
            <span>Log Aktivitas</span>
        </a>
        <a href="{{ route('admin.reports.index') }}" class="sidebar-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-circle"></i>
            <span>Laporan Masalah</span>
        </a>
        
        <!-- Logout -->
        <div class="sidebar-divider"></div>
        <a href="#" class="sidebar-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="bi bi-box-arrow-left"></i>
            <span>Logout</span>
        </a>
        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
    
    <!-- Content Area -->
    <div class="content-wrapper" id="content-wrapper">
        <!-- Header -->
        <div class="header">
            <div class="d-flex align-items-center">
                <button class="toggle-sidebar me-3" id="toggle-sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <div class="header-search d-none d-md-block">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" placeholder="Cari...">
                </div>
            </div>
            
            <div class="dropdown user-dropdown">
                <a class="dropdown-toggle text-decoration-none d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                    <span class="me-2 d-none d-lg-inline">{{ Auth::guard('admin')->user()->name ?? Auth::user()->name }}</span>
                    @php
                        $user = Auth::guard('admin')->user() ?? Auth::user();
                    @endphp
                    @if($user && $user->profile_picture)
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->name }}" class="rounded-circle" width="32" height="32">
                    @else
                        <div class="bg-secondary rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <span>{{ substr($user->name ?? 'U', 0, 1) }}</span>
                        </div>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person-circle me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-left me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            @yield('content')
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div>&copy; {{ date('Y') }} AKAR. All rights reserved.</div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            document.getElementById('toggle-sidebar').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('collapsed');
                document.getElementById('content-wrapper').classList.toggle('expanded');
            });
        });
    </script>
    
    <!-- Additional Scripts -->
    @yield('scripts')
</body>
</html> 