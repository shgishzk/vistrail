<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('Admin Dashboard')) | {{ config('app.name') }}</title>
    @vite(['resources/js/admin.js', 'resources/css/admin.css'])
</head>
<body class="d-flex bg-light">
    <div class="sidebar sidebar-lg sidebar-dark sidebar-fixed sidebar-self-hiding-md border-end px-xl-4 docs-sidebar elevation-0" id="sidebar">
        <div class="sidebar-header border-bottom">
            <div class="sidebar-brand">@lang('Online Territory') @lang('Admin')</div>
        </div>
        <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="nav-icon cil-speedometer"></i> @lang('Dashboard')
                </a>
            </li>

            <li class="nav-title">@lang('Users Management')</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                    <i class="nav-icon cil-user"></i> @lang('Users')
                </a>
            </li>

            <li class="nav-title">@lang('Territory Management')</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.areas*') ? 'active' : '' }}" href="{{ route('admin.areas') }}">
                    <i class="nav-icon cil-folder-open"></i> @lang('Areas')
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.visits*') ? 'active' : '' }}" href="{{ route('admin.visits') }}">
                    <i class="nav-icon cil-briefcase"></i> @lang('Visits')
                </a>
            </li>

            <li class="nav-title">@lang('Building Management')</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.buildings*') ? 'active' : '' }}" href="{{ route('admin.buildings') }}">
                    <i class="nav-icon cil-building"></i> @lang('Buildings')
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.groups*') ? 'active' : '' }}" href="{{ route('admin.groups') }}">
                    <i class="nav-icon cil-layers"></i> @lang('Groups')
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.rooms*') ? 'active' : '' }}" href="{{ route('admin.rooms') }}">
                    <i class="nav-icon cil-list"></i> @lang('Rooms')
                </a>
            </li>

            <li class="nav-title">@lang('Communication')</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.news*') ? 'active' : '' }}" href="{{ route('admin.news') }}">
                    <i class="nav-icon cil-newspaper"></i> @lang('News')
                </a>
            </li>

            <li class="nav-title">@lang('Account Management')</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.admins*') ? 'active' : '' }}" href="{{ route('admin.admins') }}">
                    <i class="nav-icon cil-people"></i> @lang('Admins')
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.action_logs*') ? 'active' : '' }}" href="{{ route('admin.action_logs') }}">
                    <i class="nav-icon cil-list-rich"></i> @lang('Action Logs')
                </a>
            </li>
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
                <ul class="header-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}"
                            href="{{ route('admin.settings') }}"
                            title="@lang('Settings')"
                            aria-label="@lang('Settings')"
                        >
                            <i class="nav-icon cil-settings"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <span class="nav-link d-inline-flex align-items-center gap-2">
                            <i class="nav-icon cil-user"></i>
                            <span>{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span>
                        </span>
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
    @stack('scripts')
</body>
</html>
