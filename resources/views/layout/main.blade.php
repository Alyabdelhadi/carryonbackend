<?php
if(Session::has('locale') && Session::get('locale') == "ae")
{
  $dir = "rtl";
  $css = "css-rtl";
}
else
{
  $dir = "ltr";
  $css = "css";
}
?>
<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="{{ $dir }}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
<title>@yield('title')</title>
<link rel="apple-touch-icon" href="{{ Asset('app-assets/images/ico/apple-icon-120.png') }}">
<link rel="shortcut icon" type="image/x-icon" href="{{ Asset('app-assets/images/ico/favicon.ico') }}">
<link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600" rel="stylesheet') }}">

<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/vendors/css/vendors.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/vendors/css/charts/apexcharts.css') }}">
<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/vendors/css/extensions/tether-theme-arrows.css') }}">
<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/vendors/css/extensions/tether.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/vendors/css/extensions/shepherd-theme-default.css') }}">
<link rel="stylesheet" type="text/css" href="{{Asset('app-assets/vendors/css/extensions/sweetalert2.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{Asset('app-assets/css/plugins/extensions/ext-component-toastr.min.css')}}">
<link rel="stylesheet" type="text/css" href="{{Asset('app-assets/dashboard-ecommerce.min.css')}}">


<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/'.$css.'/bootstrap.css') }}">
<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/'.$css.'//bootstrap-extended.css') }}">
<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/'.$css.'//colors.css') }}">
<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/'.$css.'//components.css') }}">
<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/'.$css.'//themes/dark-layout.css') }}">
<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/'.$css.'//themes/semi-dark-layout.css') }}">

<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/'.$css.'//core/menu/menu-types/vertical-menu.css') }}">
<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/'.$css.'//core/colors/palette-gradient.css') }}">
<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/'.$css.'//pages/dashboard-analytics.css') }}">
<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/'.$css.'//pages/card-analytics.css') }}">
<link rel="stylesheet" type="text/css" href="{{ Asset('app-assets/'.$css.'//plugins/tour/tour.css') }}">

<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
<style>
    /* Speed up lightbox fade animation */
    .lightboxOverlay,
    .lightbox {
        transition-duration: 0.2s !important; /* default is 0.5s */
    }
</style>

<link rel="stylesheet" type="text/css" href="{{ Asset('assets/'.$css.'//style.css') }}">
@yield('css')

</head>


<body class="vertical-layout vertical-menu-modern semi-dark-layout 2-columns  navbar-floating footer-static  " data-open="click" data-menu="vertical-menu-modern" data-col="2-columns" data-layout="semi-dark-layout" style="background: #f4f6f9;">


@include('layout.header')

@include('layout.menu')

<div class="app-content content">
<div class="content-overlay"></div>
<div class="header-navbar-shadow"></div>
<div class="content-wrapper">
<div class="content-header row">
</div>
<div class="content-body">

@if(Session::has('message'))
<div class="alert alert-success mt-1 alert-validation-msg" role="alert">
<i class="feather icon-check-circle mr-1 align-middle"></i>
<span>{{ Session::get('message') }}</span>
</div>
@endif

@if(Session::has('error'))
<div class="alert alert-danger mt-1 alert-validation-msg" role="alert">
<i class="feather icon-info mr-1 align-middle"></i>
<span>{{ Session::get('error') }}</span>
</div>
@endif

@yield('content')

</div>
</div>
</div>

<div class="sidenav-overlay"></div>
<div class="drag-target"></div>

<script src="{{ Asset('app-assets/vendors/js/vendors.min.js') }}"></script>

<script src="{{ Asset('app-assets/vendors/js/ui/jquery.sticky.js') }}"></script>
<script src="{{ Asset('app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>
<script src="{{ Asset('app-assets/vendors/js/extensions/tether.min.js') }}"></script>
<script src="{{Asset('app-assets/vendors/js/extensions/sweetalert2.all.min.js') }}"></script>


<script src="{{ Asset('app-assets/js/core/app-menu.js') }}"></script>
<script src="{{ Asset('app-assets/js/core/app.js') }}"></script>
<script src="{{ Asset('app-assets/js/scripts/components.js') }}"></script>

<script src="{{ Asset('app-assets/js/scripts/pages/dashboard-analytics.js') }}"></script>

<script src="{{ Asset('ckeditor/ckeditor.js') }}"></script>

@yield('js')

<script type="text/javascript">

CKEDITOR.replace('content');

function confirmAlert(url)
{
	Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, delete it!',
      confirmButtonClass: 'btn btn-primary',
      cancelButtonClass: 'btn btn-danger ml-1',
      buttonsStyling: false,
    }).then(function (result) {
      if (result.value) {
        Swal.fire(
          {
            type: "success",
            title: 'Deleted!',
            text: 'Your file has been deleted.',
            confirmButtonClass: 'btn btn-success',
          }
        )

        window.location = url;
      }
    })
}

</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

</body>

</html>