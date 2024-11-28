@extends('layout')
@section('title','Dash')
@section('content')
    <div class="dashboard">
        <div class="card">
            <h3>Usuarios Activos</h3>
            <p>1,234</p>
        </div>
        <div class="card">
            <h3>Transacciones Hoy</h3>
            <p>567</p>
        </div>
        <div class="card">
            <h3>Lo que putas axel quiera</h3>
            <p>246,830,407</p>
        </div>
    </div>

    <div class="graficos">
        <h2>Gráficos</h2>
        <div class="chart-container">
            <canvas id="transactionsChart"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('transactionsChart').getContext('2d');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [{
                        label: 'Transacciones por Día',
                        data: @json($chartData['values']),
                        borderColor: '#4a90e2',
                        backgroundColor: 'rgba(74, 144, 226, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
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
