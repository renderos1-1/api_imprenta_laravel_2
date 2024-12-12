
@extends('layout')

@section('title', 'Estadisticas')

@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Estadísticas</title>

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<br>
<br>
<br>
<br>
<br>



<div class="min-h-screen bg-gray-50">
    <div class="flex-1">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 pt-9">

            <div id="app" class="w-full"></div>
        </div>
    </div>
</div>
</body>
</html>
@endsection
