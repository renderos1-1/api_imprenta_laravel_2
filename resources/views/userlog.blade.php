@extends('layout')

@section('title', 'Registro de Actividades')

@push('styles')
    <style>
        .contenedor {
            padding-left: 250px;
            padding-top: 50px;
            margin-right: 30px;
        }

        .title-section {
            margin-bottom: 20px;
        }

        .title-section h2 {
            color: #333;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .title-section small {
            color: #666;
            font-size: 0.9rem;
        }

        .table-container {
            overflow-x: auto;
            background-color: #ffffff;
            border: 2px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        th {
            padding: 12px;
            text-align: left;
            background-color: #f4f6f8;
            font-weight: bold;
            color: #333;
            border: 1px solid #ddd;
        }

        td {
            padding: 12px;
            text-align: left;
            color: #555;
            border: 1px solid #ddd;
        }

        tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        tbody tr:hover {
            background-color: #e8f4fd;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 500;
            display: inline-block;
        }

        .status-login {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-logout {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .dui-text {
            font-family: monospace;
            background-color: #f4f6f8;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .timestamp {
            color: #666;
            font-size: 0.9em;
        }

        .ip-address {
            color: #666;
            font-family: monospace;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .contenedor {
                padding-left: 20px;
                padding-right: 20px;
            }

            .table-container {
                margin-left: -20px;
                margin-right: -20px;
                border-radius: 0;
                border-left: none;
                border-right: none;
            }

            td, th {
                padding: 8px;
                font-size: 0.9em;
            }
        }
    </style>
@endpush

@section('content')
    <br>
    <br>
    <br>
    <br>
    <br>
    <div class="contenedor">
        <div class="title-section">
            <h2>Registro de Actividades
                <small>(Últimas 20 actividades)</small>
            </h2>
        </div>

        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th>Fecha y Hora</th>
                    <th>DUI</th>
                    <th>Nombre</th>
                    <th>Acción</th>
                    <th>IP</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="timestamp">
                            @if(is_string($log->created_at))
                                {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                            @else
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            @endif
                        </td>
                        <td>
                            <span class="dui-text">{{ $log->dui }}</span>
                        </td>
                        <td>{{ $log->full_name ?? 'Usuario no encontrado' }}</td>
                        <td>
                                <span class="status-badge {{ $log->action === 'login' ? 'status-login' : 'status-logout' }}">
                                    {{ $log->action === 'login' ? 'Inicio de sesión' : 'Cierre de sesión' }}
                                </span>
                        </td>
                        <td class="ip-address">{{ $log->ip_address }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
