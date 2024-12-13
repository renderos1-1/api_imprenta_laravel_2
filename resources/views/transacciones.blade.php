@extends('layout')

@section('title', 'Transacciones')

@section('content')
    <br>
    <br>
    <br>
    <br>
    <br>
    <div class="contenedor">
        <h1>Buscar en la Base de Datos</h1>
        <br>

        {{-- Mensaje informativo --}}
        @if(!request('search_dui') && !request('search_name'))
            <div class="alert alert-info" role="alert">
                Mostrando las 50 transacciones más recientes. Utilice la búsqueda para encontrar transacciones específicas.
            </div>
        @endif

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

                    {{-- Botón para limpiar búsqueda --}}
                    @if(request('search_dui') || request('search_name'))
                        <a href="{{ route('transacciones') }}" class="btn btn-secondary">Limpiar búsqueda</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Contador de resultados para búsquedas --}}
        @if(request('search_dui') || request('search_name'))
            <div class="results-info">
                <p>Resultados encontrados: {{ $transactions->count() }}</p>
            </div>
        @endif

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

    {{-- Añadimos estilos adicionales --}}
    <style>
        .alert {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 4px;
        }

        .alert-info {
            background-color: #e3f2fd;
            border: 1px solid #90caf9;
            color: #1565c0;
        }

        .results-info {
            margin: 10px 0;
            font-size: 0.9em;
            color: #666;
        }

        .btn-secondary {
            margin-left: 10px;
            padding: 8px 15px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
@endsection
