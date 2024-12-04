@extends('layout')
@section('title','Estadísticas')
@section('content')
    <div class="statistics-container">
        <div class="statistics-header">
            <h1>Estadísticas del Sistema</h1>
            <br>
            <br>
            <!-- Date Range Selector -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <label class="form-label">Rango de Fechas:</label>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex gap-2">
                                <div class="input-group">
                                    <span class="input-group-text">Desde</span>
                                    <input type="date" id="global-start-date"
                                           class="form-control"
                                           value="{{ date('Y-m-d', strtotime('-7 days')) }}">
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Hasta</span>
                                    <input type="date" id="global-end-date"
                                           class="form-control"
                                           value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" onclick="updateAllGraphs()">
                                Actualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="statistics-grid">
            <!-- Top Row - Revenue Chart -->
            <div class="chart-container wide">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Ingresos en el Tiempo</h3>
                    <button class="btn btn-outline-primary" onclick="openExportModal('revenue')">
                        <i class="fas fa-download"></i> Exportar
                    </button>

                </div>
                <br>
                <canvas id="revenueChart"></canvas>
            </div>

            <!-- Bottom Row - Person Type Chart -->
            <div class="chart-container wide">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Distribución por tipo de persona</h3>
                    <button class="btn btn-outline-primary" onclick="openExportModal('personType')">
                        <i class="fas fa-download"></i> Exportar
                    </button>

                </div>
                <br>
                <canvas id="personTypeChart"></canvas>
            </div>

            <!-- Document Type Chart -->
            <div class="chart-container wide">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Distribución por tipo de documento</h3>
                    <button class="btn btn-outline-primary" onclick="openExportModal('documentType')">
                        <i class="fas fa-download"></i> Exportar
                    </button>

                </div>
                <br>
                <canvas id="documentTypeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Export Modals -->
    @foreach(['revenue', 'personType', 'documentType'] as $type)
        <div class="modal fade" id="exportModal-{{ $type }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Exportar Datos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Formato de Exportación</label>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary" onclick="exportGraph('{{ $type }}', 'pdf')">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                                <button class="btn btn-outline-primary" onclick="exportGraph('{{ $type }}', 'xlsx')">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button class="btn btn-outline-primary" onclick="exportGraph('{{ $type }}', 'csv')">
                                    <i class="fas fa-file-csv"></i> CSV
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/statistics-charts.js') }}"></script>
    <script>
        let statisticsCharts;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts with data from blade
            statisticsCharts = new StatisticsCharts();
            const initialData = {
                revenue: @json($revenueChartData),
                personType: @json($pieChartData),
                documentType: @json($documentTypeChartData)
            };
            statisticsCharts.initialize(initialData);
        });

        // Global update function
        function updateAllGraphs() {
            const startDate = document.getElementById('global-start-date').value;
            const endDate = document.getElementById('global-end-date').value;
            statisticsCharts.updateCharts(startDate, endDate);
        }

        // Export functions
        function openExportModal(type) {
            const modal = new bootstrap.Modal(document.getElementById(`exportModal-${type}`));
            modal.show();
        }

        function exportGraph(type, format) {
            StatisticsExport.exportChart(type, format);
        }
    </script>
@endpush
