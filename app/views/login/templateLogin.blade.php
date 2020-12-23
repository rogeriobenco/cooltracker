<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
        <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta name="viewport" content="width=device-width" />
        <meta name="description" content="Rastrei suas encomendas via Correios de forma fácil e automática">
        <meta name="author" content="Bencosys">
        <meta name="keyword" content="Tracker, Mail, Package, Correios, Encomenda, Rastreio, App, Bencosys">
        <title>Cooltracker - O jeito fácil de rastrear suas encomendas</title>

        <!-- Add to homescreen for Chrome on Android -->
        <meta name="mobile-web-app-capable" content="yes">
        <link rel="icon" sizes="192x192" href="images/android-desktop.png">

        <!-- Add to homescreen for Safari on iOS -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="Material Design Lite">
        <link rel="apple-touch-icon-precomposed" href="images/ios-desktop.png">

        <!-- Tile icon for Win8 (144x144 + tile color) -->
        <meta name="msapplication-TileImage" content="images/touch/ms-touch-icon-144x144-precomposed.png">
        <meta name="msapplication-TileColor" content="#3372DF">

        <!-- link rel="shortcut icon" href="images/favicon.png" -->

        <!--  Fonts and icons     -->
        <link href="http://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
        <link href='http://fonts.googleapis.com/css?family=Roboto:400,700,300|Material+Icons' rel='stylesheet' type='text/css'>

        <!--  CSS     -->
        {{ HTML::style('assets/css/material.cyan-light_blue.min.css') }}
        {{ HTML::style('assets/css/styles.css') }}

        <style>
            #view-source {
                position: fixed;
                display: block;
                right: 0;
                bottom: 0;
                margin-right: 40px;
                margin-bottom: 40px;
                z-index: 900;
            }
        </style>

    </head>
    <body>
        
        <div class="demo-layout mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">

            <!-- conteudo principal -->
            @yield('content');
            <!-- fim conteudo principal -->

        </div>

        <!--   Core JS Files   -->
        {{ HTML::script('assets/js/jquery-3.2.1.min.js') }}
        {{ HTML::script('assets/js/material_mdl.min.js') }}
        <!-- Page Level Script -->
        @yield('scripts')

    </body>

</html>