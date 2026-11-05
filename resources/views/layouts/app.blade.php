<!DOCTYPE html>
<html
    lang="pt-BR"
    data-layout-mode="default"
    data-menu-color="light"
    data-topbar-color="light"
    data-sidenav-user="true"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Desafio Vercan')</title>

    @vite([
        'resources/css/ubold.css',
        'resources/js/ubold.js',
        'resources/js/app.js'
    ])

    <link rel="shortcut icon" href="{{ Vite::asset('resources/images/ubold/favicon.ico') }}">

    @stack('styles')
</head>
<body>
    <!-- Begin page -->
    <div id="wrapper">

        @include('ubold.partials._menu')

            <div class="content-page">

                @include('ubold.partials._topbar')

            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">

                        @include('ubold.partials._page-title', ['subtitle' => 'Visão geral', 'title' => 'Painel'])

                        @yield('content')

                </div> <!-- container -->

            </div> <!-- content -->

            @include('ubold.partials._footer')

        </div>

    </div>
    <!-- END wrapper -->

    @include('ubold.partials._right-sidebar')

</body>
</html>
