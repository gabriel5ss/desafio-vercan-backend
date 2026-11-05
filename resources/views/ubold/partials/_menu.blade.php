<!-- ========== Menu ========== -->
<div class="app-menu">

    <!-- Brand Logo -->
    <div class="logo-box">
        <!-- Brand Logo Light -->
        <a href="{{ route('dashboard') }}" class="logo-light">
            <img src="{{ Vite::asset('resources/images/ubold/logo-light.png') }}" alt="logo" class="logo-lg">
            <img src="{{ Vite::asset('resources/images/ubold/logo-sm.png') }}" alt="small logo" class="logo-sm">
        </a>

        <!-- Brand Logo Dark -->
        <a href="{{ route('dashboard') }}" class="logo-dark">
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
                <a href="{{ route('dashboard') }}" class="menu-link">
                    <span class="menu-icon"><i data-feather="calendar"></i></span>
                    <span class="menu-text"> Home </span>
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('suppliers.index') }}" class="menu-link">
                    <span class="menu-icon"><i data-feather="users"></i></span>
                    <span class="menu-text"> Fornecedores </span>
                </a>
            </li>

            <li class="menu-title">Logout</li>

            <li class="menu-item">
                <button type="submit" form="logout-form" class="menu-link w-100 border-0 bg-transparent text-start">
                    <span class="menu-icon"><i data-feather="log-out"></i></span>
                    <span class="menu-text"> Sair </span>
                </button>
                <form id="logout-form" method="POST" action="{{ route('logout') }}" hidden>
                    @csrf
                </form>
            </li>
        </ul>
        <!--- End Menu -->
        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left menu End ========== -->
