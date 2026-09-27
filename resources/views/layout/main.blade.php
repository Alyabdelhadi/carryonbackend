@php
    $rtl = Session::get('locale') === 'ae';
    $css = $rtl ? 'css-rtl' : 'css';
    $admin = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="{{ $rtl ? 'ar' : 'en' }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}" data-textdirection="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title') · CarryOn Admin</title>
<link rel="icon" type="image/png" href="{{ Asset('assets/admin/logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="{{ Asset('app-assets/vendors/css/vendors.min.css') }}">
<link rel="stylesheet" href="{{ Asset('app-assets/vendors/css/extensions/sweetalert2.min.css') }}">
<link rel="stylesheet" href="{{ Asset('app-assets/'.$css.'/bootstrap.css') }}">
<link rel="stylesheet" href="{{ Asset('app-assets/'.$css.'/bootstrap-extended.css') }}">
<link rel="stylesheet" href="{{ Asset('app-assets/'.$css.'/colors.css') }}">
<link rel="stylesheet" href="{{ Asset('app-assets/'.$css.'/components.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
<link rel="stylesheet" href="{{ Asset('assets/admin/carryon.css') }}?v={{ @filemtime(base_path('assets/admin/carryon.css')) }}">
@yield('css')
</head>

<body class="co-body">

@include('layout.sidebar')

<div class="co-main">
    @include('layout.topbar')

    <main class="co-content">
        @if(Session::has('message'))
            <div class="co-toast co-toast-success" role="status">
                <i class="feather icon-check-circle"></i><span>{{ Session::get('message') }}</span>
                <button type="button" class="co-toast-close" aria-label="Close">&times;</button>
            </div>
        @endif
        @if(Session::has('error'))
            <div class="co-toast co-toast-danger" role="alert">
                <i class="feather icon-alert-circle"></i><span>{{ Session::get('error') }}</span>
                <button type="button" class="co-toast-close" aria-label="Close">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>
</div>
<div class="co-backdrop" data-co-sidebar-close></div>

<script src="{{ Asset('app-assets/vendors/js/vendors.min.js') }}"></script>
<script src="{{ Asset('app-assets/vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
<script src="{{ Asset('ckeditor/ckeditor.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script src="{{ Asset('assets/admin/carryon.js') }}?v={{ @filemtime(base_path('assets/admin/carryon.js')) }}"></script>
@yield('js')
</body>
</html>
