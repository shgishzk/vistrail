<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@lang('Admin Dashboard')</title>
    @vite(['resources/css/admin.css'])
</head>
<body class="c-app">
    <div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">
        <div class="c-sidebar-brand d-lg-down-none">
            <h5 class="m-0">Vistrail Admin</h5>
        </div>
        <ul class="c-sidebar-nav">
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link c-active" href="{{ route('admin.dashboard') }}">
                    <i class="c-sidebar-nav-icon cil-speedometer"></i> @lang('Dashboard')
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="c-sidebar-nav-link">
                        <i class="c-sidebar-nav-icon cil-account-logout"></i> @lang('Logout')
                    </button>
                </form>
            </li>
        </ul>
    </div>
    <div class="c-wrapper c-fixed-components">
        <header class="c-header c-header-light c-header-fixed c-header-with-subheader">
            <button class="c-header-toggler c-class-toggler d-lg-none mfe-auto" type="button" data-target="#sidebar" data-class="c-sidebar-show">
                <i class="c-icon c-icon-lg cil-menu"></i>
            </button>
            <button class="c-header-toggler c-class-toggler mfs-3 d-md-down-none" type="button" data-target="#sidebar" data-class="c-sidebar-lg-show" responsive="true">
                <i class="c-icon c-icon-lg cil-menu"></i>
            </button>
            <ul class="c-header-nav ml-auto mr-4">
                <li class="c-header-nav-item dropdown">
                    <span class="c-header-nav-link">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span>
                </li>
            </ul>
        </header>
        <div class="c-body">
            <main class="c-main">
                <div class="container-fluid">
                    <div class="fade-in">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">@lang('Admin Dashboard')</div>
                                    <div class="card-body">
                                        <p>@lang('Welcome to the Vistrail Admin Dashboard')</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
