@extends('layout')
@section('title','Estadísticas Página 2')
@section('content')
    <div class="grid-container">
        <!-- Cada div representa un "item" dentro de la cuadrícula -->
        <div class="grid-item">Item 1</div>
        <div class="grid-item"><img src="../../../api_imprenta_laravel_2/public/img/Logo_Gobierno.png" alt="logo"></div>
        <div class="grid-item">
            <div>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium at autem debitis, dignissimos
                doloremque dolorum, ea, fuga fugit incidunt officia quis quo saepe soluta totam voluptates! Accusamus
                eligendi modi vel.
            </div>80</div>
        <div class="grid-item">
            <div>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Animi blanditiis cum explicabo fuga, iusto
                minus nemo nobis numquam officia officiis pariatur qui ratione suscipit tenetur ullam ut veniam
                voluptatum. Dignissimos?
            </div>00</div>
        <div class="grid-item">Item 5</div>
        <div class="grid-item">ouyet2fiuyfqerouyfgpiur2gfogroifgyoiuqgfourufubrduroquryfouby1ofuy</div>
    </div>
    <a href="{{ route('estadisticas') }}" class="btn-next">Anterior</a>
@endsection
