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
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add console logs to debug
            console.log('Chart initialization starting');
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');

            // Log the data we're receiving
            const labels = {!! json_encode($revenueChartData['labels']) !!};
            const values = {!! json_encode($revenueChartData['values']) !!};

            console.log('Labels:', labels);
            console.log('Values:', values);

            // Make sure the canvas is properly sized
            const canvas = document.getElementById('revenueChart');
            console.log('Canvas dimensions:', canvas.width, canvas.height);

            try {
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ingresos Diarios',
                            data: values,
                            borderColor: '#0ea5e9',
                            backgroundColor: 'rgba(14, 165, 233, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointStyle: 'circle',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            title: {
                                display: true,
                                text: 'Ingresos en el Tiempo'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toFixed(2);
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('Chart initialized successfully');
            } catch (error) {
                console.error('Error creating chart:', error);
            }
        });
    </script>
@endpush
