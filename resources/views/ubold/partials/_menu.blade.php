<!-- ========== Menu ========== -->
<div class="app-menu">

    <!-- Brand Logo -->
    <div class="logo-box">
        <!-- Brand Logo Light -->
        <a href="{{ route('static') }}" class="logo-light">
            <img src="{{ Vite::asset('resources/images/ubold/logo-light.png') }}" alt="logo" class="logo-lg">
            <img src="{{ Vite::asset('resources/images/ubold/logo-sm.png') }}" alt="small logo" class="logo-sm">
        </a>

        <!-- Brand Logo Dark -->
        <a href="{{ route('static') }}" class="logo-dark">
            <img src="{{ Vite::asset('resources/images/ubold/logo-dark.png') }}" alt="dark logo" class="logo-lg">
            <img src="{{ Vite::asset('resources/images/ubold/logo-sm.png') }}" alt="small logo" class="logo-sm">
        </a>
    </div>

    <!-- menu-left -->
    <div class="scrollbar">

        <!--- Menu -->
        <ul class="menu">

            <li class="menu-title">Navigation</li>

            <li class="menu-item">
                <a href="{{ route('static') }}" class="menu-link">
                    <span class="menu-icon"><i data-feather="calendar"></i></span>
                    <span class="menu-text"> Home </span>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i data-feather="users"></i></span>
                    <span class="menu-text"> Fornecedores </span>
                </a>
            </li>

            <li class="menu-title">Logout</li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i data-feather="log-out"></i></span>
                    <span class="menu-text"> Sair </span>
                </a>
            </li>
        </ul>
        <!--- End Menu -->
        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left menu End ========== -->
