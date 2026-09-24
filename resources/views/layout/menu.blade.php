@php($page = Request::segment(1))

<div class="main-menu menu-fixed menu-dark menu-accordion menu-shadow" data-scroll-to-active="true">

    <div class="navbar-header">

        <ul class="nav navbar-nav flex-row">

            <li class="nav-item mr-auto">

                <a class="navbar-brand" href="{{ Asset('home') }}">

                    <h2 class="brand-text mb-0" style="color:white">
                        <i class="fa fa-clock-o"></i>
                        <span id="shwTime"></span>
                    </h2>

                </a>

            </li>

            <style type="text/css">

                .fa
                {
                    color: white !important;
                }

                .headTitle
                {
                    padding: 10px 20px;
                    color: gray;
                    margin-top: 10%;
                    margin-bottom: 5%;
                }

            </style>

            <script>

                var myVar = setInterval(myTimer, 1000);

                function myTimer()
                {
                    var d = new Date();

                    document.getElementById("shwTime").innerHTML =
                        d.toLocaleTimeString();
                }

            </script>

        </ul>

    </div>


    <div class="shadow-bottom"></div>


    <div class="main-menu-content">

        <ul
            class="navigation navigation-main"
            id="main-menu-navigation"
            data-menu="menu-navigation"
        >


            {{-- Dashboard --}}
            <li class="@if($page == 'home') active @endif nav-item">

                <a href="{{ Asset('home') }}">

                    <i class="fa fa-home"></i>

                    <span
                        class="menu-title"
                        data-i18n="Email"
                    >
                        Dashboard
                    </span>

                </a>

            </li>


            {{-- Push Notifications --}}
            <li class="@if($page == 'push') active @endif nav-item">

                <a href="{{ Asset('push') }}">

                    <i class="fa fa-bell"></i>

                    <span
                        class="menu-title"
                        data-i18n="Email"
                    >
                        Push Notifications
                    </span>

                </a>

            </li>


            {{-- Users --}}
            <li
                class="@if(
                    request()->is('users') ||
                    request()->is('users/active') ||
                    request()->is('users/inactive')
                ) active open @endif nav-item"
            >

                <a href="#">

                    <i class="fa fa-users"></i>

                    <span
                        class="menu-title"
                        data-i18n="Users"
                    >
                        Users
                    </span>

                </a>


                <ul class="menu-content">


                    <li class="{{ request()->is('users') ? 'active' : '' }}">

                        <a href="{{ Asset('users') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                All Users
                            </span>

                        </a>

                    </li>


                    <li class="{{ request()->is('users/active') ? 'active' : '' }}">

                        <a href="{{ Asset('users/active') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                Active Users
                            </span>

                        </a>

                    </li>


                    <li class="{{ request()->is('users/inactive') ? 'active' : '' }}">

                        <a href="{{ Asset('users/inactive') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                Inactive Users
                            </span>

                        </a>

                    </li>


                </ul>

            </li>


            {{-- Trips --}}
            <li
                class="@if(
                    request()->is('trips') ||
                    request()->is('trips/one-time') ||
                    request()->is('trips/frequent') ||
                    request()->is('trips/upcoming-routes') ||
                    request()->is('trips/upcoming-country-routes')
                ) active open @endif nav-item"
            >

                <a href="#">

                    <i class="fa fa-plane"></i>

                    <span
                        class="menu-title"
                        data-i18n="Trips"
                    >
                        Trips
                    </span>

                </a>


                <ul class="menu-content">


                    <li class="{{ request()->is('trips') ? 'active' : '' }}">

                        <a href="{{ Asset('trips') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                All Trips
                            </span>

                        </a>

                    </li>


                    <li class="{{ request()->is('trips/one-time') ? 'active' : '' }}">

                        <a href="{{ Asset('trips/one-time') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                One-Time Trips
                            </span>

                        </a>

                    </li>


                    <li class="{{ request()->is('trips/frequent') ? 'active' : '' }}">

                        <a href="{{ Asset('trips/frequent') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                Frequent Routes
                            </span>

                        </a>

                    </li>


                    <li class="{{ request()->is('trips/upcoming-routes') ? 'active' : '' }}">

                        <a href="{{ Asset('trips/upcoming-routes') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                Upcoming City Routes
                            </span>

                        </a>

                    </li>


                    <li class="{{ request()->is('trips/upcoming-country-routes') ? 'active' : '' }}">

                        <a href="{{ Asset('trips/upcoming-country-routes') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                Upcoming Country Routes
                            </span>

                        </a>

                    </li>


                </ul>

            </li>


            {{-- Packages --}}
            <li class="@if($page == 'parcel_order') active open @endif nav-item">

                <a href="#">

                    <i class="fa fa-shopping-cart"></i>

                    <span
                        class="menu-title"
                        data-i18n="Dashboard"
                    >
                        Packages
                    </span>

                </a>


                <ul class="menu-content">


                    <li>
                        <a href="{{ Asset('parcel_order?status=0') }}">

                            <i class="feather icon-circle"></i>

                            <span
                                class="menu-item"
                                data-i18n="Analytics"
                            >
                                Unassigned Packages
                            </span>

                        </a>
                    </li>


                    <li>
                        <a href="{{ Asset('parcel_order?status=1') }}">

                            <i class="feather icon-circle"></i>

                            <span
                                class="menu-item"
                                data-i18n="Analytics"
                            >
                                Running Packages
                            </span>

                        </a>
                    </li>


                    <li>
                        <a href="{{ Asset('parcel_order?status=2') }}">

                            <i class="feather icon-circle"></i>

                            <span
                                class="menu-item"
                                data-i18n="Analytics"
                            >
                                Delivered Packages
                            </span>

                        </a>
                    </li>


                    <li>
                        <a href="{{ Asset('parcel_order?status=3') }}">

                            <i class="feather icon-circle"></i>

                            <span
                                class="menu-item"
                                data-i18n="Analytics"
                            >
                                Cancelled Packages
                            </span>

                        </a>
                    </li>


                    <li>
                        <a href="{{ Asset('parcel_order?status=4') }}">

                            <i class="feather icon-circle"></i>

                            <span
                                class="menu-item"
                                data-i18n="Analytics"
                            >
                                Expired Packages
                            </span>

                        </a>
                    </li>


                    <li>
                        <a href="{{ Asset('parcel_order?status=all') }}">

                            <i class="feather icon-circle"></i>

                            <span
                                class="menu-item"
                                data-i18n="Analytics"
                            >
                                All Packages
                            </span>

                        </a>
                    </li>


                </ul>

            </li>


            {{-- Settings --}}
            <li
                class="@if(
                    $page == 'setting' ||
                    $page == 'payment-methods' ||
                    $page == 'versions' ||
                    $page == 'sliders' ||
                    $page == 'sliders2' ||
                    $page == 'services' ||
                    $page == 'parcel_cate' ||
                    $page == 'weights' ||
                    $page == 'tips' ||
                    $page == 'texts' ||
                    $page == 'delivery_statuses'
                ) active open @endif nav-item"
            >

                <a href="#">

                    <i class="fa fa-cog"></i>

                    <span
                        class="menu-title"
                        data-i18n="Settings"
                    >
                        Settings
                    </span>

                </a>


                <ul class="menu-content">


                    {{-- Configuration --}}
                    <li class="{{ $page == 'setting' ? 'active' : '' }}">

                        <a href="{{ Asset('setting') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                Configuration
                            </span>

                        </a>

                    </li>


                    {{-- Payment Methods --}}
                    <li class="{{ $page == 'payment-methods' ? 'active' : '' }}">

                        <a href="{{ Asset('payment-methods') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                Payment Methods
                            </span>

                        </a>

                    </li>


                    {{-- App Settings --}}
                    <li class="{{ $page == 'app-settings' ? 'active' : '' }}">

                        <a href="{{ url('app-settings') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                App Settings
                            </span>

                        </a>

                    </li>


                    {{-- App Versions --}}
                    <li class="{{ $page == 'versions' ? 'active' : '' }}">

                        <a href="{{ url('versions') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                App Versions
                            </span>

                        </a>

                    </li>


                    {{-- App Sliders --}}
                    <li class="{{ $page == 'sliders' ? 'active' : '' }}">

                        <a href="{{ Asset('sliders') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                App Sliders
                            </span>

                        </a>

                    </li>


                    {{-- App Banners --}}
                    <li class="{{ $page == 'sliders2' ? 'active' : '' }}">

                        <a href="{{ Asset('sliders2') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                App Banners
                            </span>

                        </a>

                    </li>


                    {{-- Services --}}
                    <li class="{{ $page == 'services' ? 'active' : '' }}">

                        <a href="{{ Asset('services') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                Services
                            </span>

                        </a>

                    </li>


                    {{-- Package Categories --}}
                    <li class="{{ $page == 'parcel_cate' ? 'active' : '' }}">

                        <a href="{{ Asset('parcel_cate') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                Package Categories
                            </span>

                        </a>

                    </li>


                    {{-- Weights --}}
                    <li class="{{ $page == 'weights' ? 'active' : '' }}">

                        <a href="{{ Asset('weights') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                Weights
                            </span>

                        </a>

                    </li>


                    {{-- Tips --}}
                    <li class="{{ $page == 'tips' ? 'active' : '' }}">

                        <a href="{{ Asset('tips') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                Tips
                            </span>

                        </a>

                    </li>


                    {{-- App Texts --}}
                    <li class="{{ $page == 'texts' ? 'active' : '' }}">

                        <a href="{{ Asset('texts') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                App Texts
                            </span>

                        </a>

                    </li>


                    {{-- Delivery Statuses --}}
                    <li class="{{ $page == 'delivery_statuses' ? 'active' : '' }}">

                        <a href="{{ Asset('delivery_statuses') }}">

                            <i class="feather icon-circle"></i>

                            <span class="menu-item">
                                Delivery Statuses
                            </span>

                        </a>

                    </li>


                </ul>

            </li>


            {{-- Countries --}}
            <li class="@if($page == 'countries') active @endif nav-item">

                <a href="{{ Asset('countries') }}">

                    <i class="fa fa-th-large"></i>

                    <span
                        class="menu-title"
                        data-i18n="Email"
                    >
                        Countries
                    </span>

                </a>

            </li>


            {{-- Cities --}}
            <li class="@if($page == 'cities') active @endif nav-item">

                <a href="{{ Asset('cities') }}">

                    <i class="fa fa-th-large"></i>

                    <span
                        class="menu-title"
                        data-i18n="Email"
                    >
                        Cities
                    </span>

                </a>

            </li>


            {{-- Pages --}}
            <li class="@if($page == 'pages') active @endif nav-item">

                <a href="{{ Asset('pages') }}">

                    <i class="fa fa-file"></i>

                    <span
                        class="menu-title"
                        data-i18n="Email"
                    >
                        Pages
                    </span>

                </a>

            </li>


            {{-- Notification Templates --}}
            <li class="@if($page == 'notifications') active @endif nav-item">

                <a href="{{ Asset('notifications') }}">

                    <i class="fa fa-bell"></i>

                    <span
                        class="menu-title"
                        data-i18n="Dashboard"
                    >
                        Notification Templates
                    </span>

                </a>

            </li>


            {{-- Email Templates --}}
            <li class="@if($page == 'emails') active @endif nav-item">

                <a href="{{ Asset('emails') }}">

                    <i class="fa fa-envelope"></i>

                    <span
                        class="menu-title"
                        data-i18n="Dashboard"
                    >
                        Email Templates
                    </span>

                </a>

            </li>


            {{-- Logout --}}
            <li class="nav-item">

                <a href="{{ Asset('logout') }}">

                    <i class="fa fa-sign-out"></i>

                    <span
                        class="menu-title"
                        data-i18n="Email"
                    >
                        Logout
                    </span>

                </a>

            </li>


        </ul>

    </div>

</div>