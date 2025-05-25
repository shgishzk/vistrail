<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vistrail Admin')</title>
    @vite(['resources/js/admin.js', 'resources/css/admin.css'])
</head>
<body class="d-flex bg-light">
    <div class="sidebar sidebar-lg sidebar-dark sidebar-fixed sidebar-self-hiding-md border-end px-xl-4 docs-sidebar elevation-0" id="sidebar">
        <div class="sidebar-header border-bottom">
            <div class="sidebar-brand">Vistrail Admin</div>
        </div>
        <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="nav-icon cil-speedometer"></i> @lang('Dashboard')
                </a>
            </li>

            <li class="nav-title">@lang('Users')</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                    <i class="nav-icon cil-user"></i> @lang('Users')
                </a>
            </li>

            <li class="nav-title">@lang('Territory')</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.areas*') ? 'active' : '' }}" href="{{ route('admin.areas') }}">
                    <i class="nav-icon cil-folder-open"></i> @lang('Areas')
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/visits">
                    <i class="nav-icon cil-briefcase"></i> @lang('Visits')
                </a>
            </li>

            <li class="nav-title">@lang('Account')</li>
            <li class="nav-item">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="nav-link border-0 bg-transparent text-start w-100">
                        <i class="nav-icon cil-account-logout"></i> @lang('Logout')
                    </button>
                </form>
            </li>
        </ul>
    </div>
    
    <div class="wrapper flex-grow-1" style="min-width: 0;">
        <header class="docs-header header header-sticky elevation-0 mb-5">
            <div class="container-fluid">
                <ul class="header-nav ms-auto">
                    <li class="nav-item dropdown">
                        <span class="nav-link">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span>
                    </li>
                </ul>
            </div>
        </header>
        <div class="body flex-grow-1 px-3">
            <div class="container-lg">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
