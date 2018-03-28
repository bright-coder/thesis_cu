<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <!--<link href="{{ asset('css/app.css') }}" rel="stylesheet"> -->

    <!-- Boostrap Preimium 4 -->
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <!-- Font Awesome CSS-->
    <link rel="stylesheet" href="{{ asset('vendor/font-awesome/css/font-awesome-all.min.css') }}">
    <!-- Fontastic Custom icon font-->
    <link rel="stylesheet" href="{{ asset('css/bootstrap4-premium/fontastic.css') }}">
    <!-- Google fonts - Poppins -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href=" {{ asset('css/bootstrap4-premium/style.blue.premium.css') }}" id="theme-stylesheet">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="{{ asset('css/bootstrap4-premium/custom.css') }}">
    <!-- include Custom CSS -->
    @yield('customCSS')
    <!-- Favicon-->
    <link rel="shortcut icon" href="img/favicon.ico">
    <!-- Tweaks for older IEs--><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
</head>
<body>
    <div class="page login-page">
            @yield('content')
    </div>

    <!-- Scripts -->
    <!--<script src="{{ asset('js/app.js') }}"></script> -->

    <!-- Boostrap Preimium 4 -->
    <!-- JavaScript files-->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/popper.js/umd/popper.min.js') }}"> </script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery.cookie/jquery.cookie.js') }}"> </script>
    {{--  <script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>  --}}
    <script src="{{ asset('vendor/jquery-validation/jquery.validate.min.js') }}"></script>
    {{--  <script src="{{ asset('js/bootstrap4-premium/charts-home.js') }}"></script>  --}}
    <!-- Notifications-->
    <script src="{{ asset('vendor/messenger-hubspot/build/js/messenger.min.js') }}">   </script>
    <script src="{{ asset('vendor/messenger-hubspot/build/js/messenger-theme-flat.js') }}">       </script>
    <script src="{{ asset('js/bootstrap4-premium/home-premium.js') }}"> </script>
    <!-- Main File-->
    <script src="{{ asset('js/bootstrap4-premium/front.js') }}"></script>
</body>
</html>
