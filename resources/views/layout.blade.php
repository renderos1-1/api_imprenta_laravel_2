<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{asset('css/imprentadash.css')}}">
    <link rel="stylesheet" href="{{asset('css/estilo.css')}}">
    <link rel="stylesheet" href="{{asset('css/adminuser.css')}}">
    <link rel="stylesheet" href="{{asset('css/userlog.css')}}">
    <link rel="stylesheet" href="{{asset('css/estadisticas.css')}}">
    @vite(['resources/js/app.js'])
    <title>@yield('Title', 'imprentadashboard')</title>
    @stack('styles')
</head>
<body class="body_principal">
@include('header')
@yield('content')
@stack('scripts')
</body>
</html>
