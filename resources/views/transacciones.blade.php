@extends('layout')

@section('title', 'Transacciones')

@section('content')
    <div class="contenedor">
        <h1>Buscar en la Base de Datos</h1>
        <div class="search-container">
            <form method="GET" action="{{ route('transacciones') }}">
                <div class="search-inputs">
                    <input type="text"
                           name="search_dui"
                           id="searchDUI"
                           placeholder="Buscar por DUI (00000000-0)"
                           value="{{ request('search_dui') }}"
                           pattern="[0-9]{8}-[0-9]"
                           title="Formato DUI: 00000000-0">

                    <input type="text"
                           name="search_name"
                           id="searchName"
                           placeholder="Buscar por Nombre"
                           value="{{ request('search_name') }}">

                    <button type="submit" class="search-button">Buscar</button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>DUI</th>
                    <th>Tipo de Persona</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
                </thead>
                <tbody id="dataTable">
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->full_name }}</td>
                        <td>{{ $transaction->document_number }}</td>
                        <td>{{ $transaction->person_type === 'persona_natural' ? 'Natural' : 'Jurídica' }}</td>
                        <td>{{ $transaction->email }}</td>
                        <td>{{ $transaction->phone }}</td>
                        <td>
                                <span class="status-badge status-{{ $transaction->status }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                        </td>
                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron registros</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="pagination-container">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <link rel="stylesheet" href="{{ asset('css/transacciones.css') }}">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validación de formato DUI
            const duiInput = document.getElementById('searchDUI');
            if (duiInput) {
                duiInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, ''); // Elimina no-números
                    if (value.length > 8) {
                        value = value.substr(0, 8) + '-' + value.substr(8, 1);
                    }
                    e.target.value = value;
                });
            }

            // Validación del formulario
            const searchForm = document.querySelector('form');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    const dui = duiInput.value;
                    if (dui && !/^\d{8}-\d$/.test(dui)) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'El formato del DUI debe ser: 00000000-0'
                        });
                    }
                });
            }
        });
    </script>

    <style>
        .search-container {
            margin: 20px 0;
        }

        .search-inputs {
            display: flex;
            gap: 10px;
            max-width: 800px;
            margin: 0 auto;
        }

        .search-inputs input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-button {
            padding: 8px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-button:hover {
            background-color: #0056b3;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .status-completado {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-en_proceso {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-cancelado {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .pagination-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
    </style>
@endsection
