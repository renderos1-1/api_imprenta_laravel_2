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


@endsection
