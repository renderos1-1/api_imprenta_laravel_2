@extends('layout')

@section('title', 'Registro de Actividades')

@push('styles')
    <style>
        .table-container {
            margin: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .titulo{
            text-align: center;
            margin-top: 50px;
            margin-left: 200px;

        }

        .container{
            margin-left: 200px;
        }


        .custom-table {
            width: 100%;
            background-color: #fff;
            border-collapse: collapse;
            font-size: 0.9em;
            border-radius: 10px;
        }

        .custom-table thead tr {
            background-color: #2c3e50;
            color: #ffffff;
            text-align: left;
            font-weight: bold;
        }

        .custom-table th,
        .custom-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dddddd;
        }

        .custom-table tbody tr {
            border-bottom: 1px solid #dddddd;
            transition: all 0.3s ease;
        }

        .custom-table tbody tr:nth-of-type(even) {
            background-color: #f3f3f3;
        }

        .custom-table tbody tr:last-of-type {
            border-bottom: 2px solid #2c3e50;
        }

        .custom-table tbody tr:hover {
            background-color: #f5f5f5;
            transform: scale(1.003);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Badges para los estados */
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .table-container {
                margin: 10px;
                overflow-x: auto;
            }

            .custom-table {
                font-size: 0.8em;
            }

            .custom-table th,
            .custom-table td {
                padding: 8px 10px;
            }
        }

        /* Estilos para la paginación */
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 20px 0;
            gap: 5px;
        }

        .pagination .page-item {
            display: inline-block;
        }

        .pagination .page-link {
            padding: 8px 16px;
            border: 1px solid #dee2e6;
            color: #2c3e50;
            background-color: #fff;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .pagination .page-item.active .page-link {
            background-color: #2c3e50;
            color: #fff;
            border-color: #2c3e50;
        }

        .pagination .page-link:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
        }

        /* Estilos para el contenedor principal */
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 400px;

        }

        .card-body {
            padding: 20px;
        }

    </style>
@endpush

@section('content')
    <h2 class="titulo">
        {{ __('Registro de Actividades') }}
    </h2>
    <div class="container">

        <div class="card">
            <div class="card-body">
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>DUI</th>
                            <th>Nombre de Usuario</th>
                            <th>Acción</th>
                            <th>Dirección IP</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>
                                    @if(is_string($log->created_at))
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                                    @else
                                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                                    @endif
                                </td>
                                <td>{{ $log->dui }}</td>
                                <td>{{ $log->user->name ?? 'N/A' }}</td>
                                <td class="text-center">
                                        <span class="badge {{ $log->action === 'login' ? 'badge-success' : 'badge-danger' }}">
                                            {{ $log->action === 'login' ? 'Inicio de sesión' : 'Cierre de sesión' }}
                                        </span>
                                </td>
                                <td>{{ $log->ip_address }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
