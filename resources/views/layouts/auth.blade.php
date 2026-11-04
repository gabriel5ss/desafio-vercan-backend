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

    @vite(['resources/css/ubold.css', 'resources/js/ubold.js'])

    <link rel="shortcut icon" href="{{ Vite::asset('resources/images/ubold/favicon.ico') }}">

    @stack('styles')
</head>
<body class="authentication-bg authentication-bg-pattern">

    <div class="account-pages mt-5 mb-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-4">
                    <div class="card bg-pattern">

                        <div class="card-body p-4">

                            <div class="text-center w-75 m-auto">
                                <div class="auth-brand">
                                    <a href="index.html" class="logo logo-dark text-center">
                                        <span class="logo-lg">
                                            <img src="{{ Vite::asset('resources/images/ubold/logo-dark.png') }}" alt="" height="22">
                                        </span>
                                    </a>

                                    <a href="index.html" class="logo logo-light text-center">
                                        <span class="logo-lg">
                                            <img src="{{ Vite::asset('resources/images/ubold/logo-light.png') }}" alt="" height="22">
                                        </span>
                                    </a>
                                </div>
                                <p class="text-muted mb-4 mt-3">Enter your email address and password to access admin panel.</p>
                            </div>

                            @yield('content')

                        </div> <!-- end card-body -->
                    </div>
                    <!-- end card -->

                    @yield('return-link')
                    <!-- end row -->

                </div> <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end page -->

    <footer class="footer footer-alt">
        2015 - {{ date('Y') }} &copy; UBold theme by <a href="" class="text-white-50">Coderthemes</a>
    </footer>

    </body>
</html>
