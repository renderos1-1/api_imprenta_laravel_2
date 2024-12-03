@extends('layout')
@section('title','Estadísticas')
@section('content')
    <div class="statistics-container">
        <div class="statistics-header">
            <h1>Estadísticas del Sistema</h1>
        </div>

        <div class="statistics-grid">
            <!-- Top Row - Wider Charts -->
            <div class="chart-container wide">
                <canvas id="revenueChart"></canvas>
            </div>

            <div class="chart-container wide">
                <canvas id="transactionsChart"></canvas>
            </div>

            <!-- Bottom Row - Smaller Charts -->
            <div class="chart-container">
                <canvas id="personTypeChart"></canvas>
            </div>

            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>

            <div class="chart-container">
                <canvas id="locationChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const revenueData = @json($revenueData);

            const ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: revenueData.map(item => item.date),
                    datasets: [
                        {
                            label: 'Ingresos Diarios',
                            data: revenueData.map(item => parseFloat(item.daily_revenue)),
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Promedio de Ingresos',
                            data: revenueData.map(item => parseFloat(item.avg_revenue)),
                            type: 'line',
                            fill: false,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            tension: 0.4,
                            yAxisID: 'y'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Análisis de Ingresos (Últimos 7 días)',
                            font: {
                                size: 16
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('es-SV', {
                                            style: 'currency',
                                            currency: 'USD'
                                        }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Fecha'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Monto ($)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('es-SV', {
                                        style: 'currency',
                                        currency: 'USD'
                                    }).format(value);
                                }
                            }
                        }
                    }
                }
            });
        });

    </script>
    @endpush
@endsection
