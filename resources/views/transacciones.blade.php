@extends('layout')

@section('title', 'Transacciones')

@section('content')
    <div class="contenedor">
        <h1>Buscar en la Base de Datos</h1>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Buscar por Nombre o DUI">
        </div>
        <table>
            <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>DUI</th>
                <th>Tipo de Persona</th>
                <th>Correo</th>
                <th>Teléfono</th>
            </tr>
            </thead>
            <tbody id="dataTable">
            <tr>
                <td>Juan Pérez</td>
                <td>00000000-1</td>
                <td>Natural</td>
                <td>juan.perez@mail.com</td>
                <td>1234-5678</td>
            </tr>
            <tr>
                <td>María López</td>
                <td>12345678-9</td>
                <td>Jurídica</td>
                <td>maria.lopez@mail.com</td>
                <td>8765-4321</td>
            </tr>
            <tr>
                <td>Carlos Gómez</td>
                <td>98765432-1</td>
                <td>Natural</td>
                <td>carlos.gomez@mail.com</td>
                <td>1357-2468</td>
            </tr>
            </tbody>
        </table>
    </div>
    <link rel="stylesheet" href="{{ asset('css/transacciones.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ mix('js/transacciones.js') }}"></script>
@endsection
