<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Cooltracker - O jeito fácil de rastrear suas encomendas</title>
        <meta name="description" content="Rastrei suas encomendas via Correios de forma fácil e automática">
        <meta name="author" content="Bencosys">
        <meta name="keyword" content="Tracker, Mail, Package, Correios, Encomenda, Rastreio, App, Bencosys">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="robots" content="all,follow">
        <!-- Bootstrap CSS-->
        <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
        <!-- Font Awesome CSS-->
        <link rel="stylesheet" href="vendor/font-awesome/css/font-awesome.min.css">
        <!-- Fontastic Custom icon font-->
        <link rel="stylesheet" href="css/fontastic.css">
        <!-- Google fonts - Poppins -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
        <!-- theme stylesheet-->
        <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
        <!-- Custom stylesheet - for your changes-->
        <link rel="stylesheet" href="css/custom.css">
        <!-- Favicon-->
        <link rel="shortcut icon" href="img/favicon.ico">
        <!-- Tweaks for older IEs--><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
    </head>
    <body>

        <div class="page">
            <!-- Main Navbar-->
            <header class="header">
                <nav class="navbar">
                    <!-- Search Box-->
                    <div class="search-box">
                        <button class="dismiss"><i class="icon-close"></i></button>
                        {{ Form::open(array('url' => '/', 'method' => 'GET', 'class' => '', 'id' => 'searchForm', 'role' => 'search')) }}
                            <input type="search" placeholder="Digite a encomenda que está procurando..." class="form-control">
                        {{ Form::close() }}
                    </div>
                    <div class="container-fluid">
                        <div class="navbar-holder d-flex align-items-center justify-content-between">
                            <!-- Navbar Header-->
                            <div class="navbar-header">
                                <!-- Navbar Brand --><a href="index.html" class="navbar-brand">
                                <div class="brand-text brand-big"><span>Cooltracker </span><strong>Rastreio</strong></div>
                                <div class="brand-text brand-small"><strong>CT</strong></div></a>
                                <!-- Toggle Button-->
                                <a id="toggle-btn" href="#" class="menu-btn active"><span></span><span></span><span></span></a>
                            </div>
                            <!-- Navbar Menu -->
                            <ul class="nav-menu list-unstyled d-flex flex-md-row align-items-md-center">
                                <!-- Search-->
                                <li class="nav-item d-flex align-items-center"><a id="search" href="#"><i class="icon-search"></i></a></li>
                                <!-- Notifications-->
                                <li class="nav-item dropdown">
                                    <a id="notifications" rel="nofollow" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link">
                                        <i class="fa fa-bell-o"></i>
                                        @yield('notifications')
                                    </a>
                                </li>
                                <!-- Logout-->
                                <li class="nav-item">
                                    <a href="{{ url('sair') }}" class="nav-link logout">Logout<i class="fa fa-sign-out"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>
            <div class="page-content d-flex align-items-stretch"> 
                <!-- Side Navbar -->
                <nav class="side-navbar">
                    <!-- Sidebar Header-->
                    <div class="sidebar-header d-flex align-items-center">
                        <div class="avatar"><img src="img/avatar.png" alt="..." class="img-fluid rounded-circle"></div>
                        <div class="title">
                            <h1 class="h4">{{ Auth::user()->nome }}</h1>
                        </div>
                    </div>

                    <!-- Sidebar Navidation Menus-->
                    <span class="heading">Menu</span>
                    <ul class="list-unstyled">
                        <li @if(true) class="active" @endif><a href="{{ url('/') }}"> <i class="icon-home"></i>Dashboard </a></li>
                        <li @if(true) class="" @endif><a href="{{ url('cadastro') }}"> <i class="icon-grid"></i>Cadastro </a></li>
                    </ul>

                </nav>

                <!-- conteudo principal -->
                @yield('content')
                <!-- fim conteudo principal -->

            </div>
        </div>



        <!-- Javascript files-->
        <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
        <script src="vendor/popper.js/umd/popper.min.js"> </script>
        <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
        <script src="vendor/jquery.cookie/jquery.cookie.js"> </script>
        <script src="vendor/chart.js/Chart.min.js"></script>
        <script src="vendor/jquery-validation/jquery.validate.min.js"></script>
        <!-- Main File-->
        <script src="js/front.js"></script>
        <!-- Page Level Script -->
        @yield('scripts')
    </body>
</html>