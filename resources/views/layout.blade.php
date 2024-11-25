<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{asset('css/imprentadash.css')}}">
    <link rel="stylesheet" href="{{asset('css/estilo.css')}}">
    <title>@yield('Title', 'imprentadashboard')</title>
</head>
<body class="body_principal">
@include('header')
@yield('content')

</body>
</html>
