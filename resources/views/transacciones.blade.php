@extends('layout')
@section('title','Transacciones')
@section('content')
    <div class="log-events">
        <h2>Registro de Eventos de Usuario</h2>
        <table>
            <thead>
            <tr>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Fecha</th>
                <th>Hora</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Juan Pérez</td>
                <td>Inició sesión</td>
                <td>2024-11-28</td>
                <td>09:00:12</td>
            </tr>
            <tr>
                <td>María López</td>
                <td>Accedió al reporte mensual</td>
                <td>2024-11-28</td>
                <td>09:15:45</td>
            </tr>
            <tr>
                <td>Carlos Gómez</td>
                <td>Descargó el informe de estadísticas</td>
                <td>2024-11-28</td>
                <td>09:30:22</td>
            </tr>
            <tr>
                <td>Juan Pérez</td>
                <td>Actualizó su perfil</td>
                <td>2024-11-28</td>
                <td>09:45:10</td>
            </tr>
            <tr>
                <td>María López</td>
                <td>Accedió a la sección de gráficos</td>
                <td>2024-11-28</td>
                <td>10:00:30</td>
            </tr>
            <tr>
                <td>Carlos Gómez</td>
                <td>Solicitó la descarga de datos</td>
                <td>2024-11-28</td>
                <td>10:15:00</td>
            </tr>
            <tr>
                <td>Ana Martínez</td>
                <td>Generó un nuevo reporte</td>
                <td>2024-11-28</td>
                <td>10:30:45</td>
            </tr>
            <tr>
                <td>Juan Pérez</td>
                <td>Cerró sesión</td>
                <td>2024-11-28</td>
                <td>10:45:55</td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
