@extends('layout')
@section('title','Dash')
@section('content')
    <div class="dashboard">
        <div class="card bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-center">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    Transacciones de Hoy
                </h3>
                <br>
                <p class="text-4xl font-bold text-indigo-600 " style="color: green">
                    {{ $todayTransactions }}
                </p>
                <br>
                <p class="text-sm text-gray-500 mt-2">
                    {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                </p>
            </div>
        </div>
        <div class="card bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Ingresos Diarios</h3>
            <br>
            <p class="text-3xl font-bold text-indigo-600" style="color: green">${{ $formattedTodayRevenue }}</p>
            <br>
            <p class="text-sm text-gray-500 mt-2">Total del día {{ \Carbon\Carbon::today()->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="graficos">

    </div>
    <div class="chart-container" id="loco">
        <canvas id="transactionsChart"></canvas>
    </div>

    {{-- New pie chart container --}}
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('transactionsChart').getContext('2d');

            const canvas = document.getElementById('loco');
            console.log('Canvas dimensions:', canvas.width, canvas.height);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [{
                        label: 'Transacciones por Día',
                        data: @json($chartData['values']),
                        borderColor: '#4a90e2',
                        backgroundColor: 'rgba(74, 144, 226, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Transacciones Diarias',
                            font: {
                                size: 16
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
