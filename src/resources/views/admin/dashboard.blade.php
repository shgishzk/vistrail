<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@lang('Admin Dashboard')</title>
    @vite(['resources/js/admin.js', 'resources/css/admin.css'])
</head>
<body class="sidebar-fixed">
    <div class="sidebar sidebar-dark sidebar-fixed" id="sidebar">
        <div class="sidebar-brand d-none d-md-flex">
            <h5 class="m-0">Vistrail Admin</h5>
        </div>
        <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                    <i class="nav-icon cil-speedometer"></i> @lang('Dashboard')
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
    
    <div class="wrapper d-flex flex-column min-vh-100">
        <header class="header header-sticky mb-4">
            <div class="container-fluid">
                <button class="header-toggler px-md-0 me-md-3 d-md-none" type="button" onclick="coreui.Sidebar.getOrCreateInstance(document.querySelector('#sidebar')).toggle()">
                    <i class="icon cil-menu"></i>
                </button>
                <ul class="header-nav ms-auto">
                    <li class="nav-item dropdown">
                        <span class="nav-link">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span>
                    </li>
                </ul>
            </div>
        </header>
        <div class="body flex-grow-1 px-3">
            <div class="container-lg">
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
    </div>
</body>
</html>
