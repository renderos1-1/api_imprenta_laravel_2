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

    </div>
    <div class="chart-container">
        <canvas id="transactionsChart"></canvas>
    </div>

    {{-- New pie chart container --}}
    <div class="chartoso">
        <h2 class="text">Distribución por Tipo de Persona</h2>
        <div class="w-full" style="max-width: 500px; margin: 0 auto;">
            <canvas id="personTypeChart"></canvas>
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

        // Add pie chart
        const personTypeCtx = document.getElementById('personTypeChart');
        new Chart(personTypeCtx, {
            type: 'pie',
            data: {
                labels: @json($pieChartData['labels']),
                datasets: [{
                    data: @json($pieChartData['values']),
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const percentage = @json($pieChartData['percentages'])[context.dataIndex];
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    </script>
@endpush
