<!-- resources/views/components/graph-export.blade.php -->
@props([
    'title',
    'graphType',
    'chartId'
])

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">{{ $title }}</h3>

            <!-- Export Button -->
            <button class="btn btn-primary"
                    type="button"
                    onclick="openExportModal('{{ $graphType }}')">
                <i class="fas fa-download"></i> Exportar
            </button>
        </div>
    </div>

    <div class="card-body">
        {{ $slot }}
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal-{{ $graphType }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exportar {{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Date Range Selection -->
                <div class="mb-3">
                    <label class="form-label">Rango de Fechas</label>
                    <div class="d-flex gap-2">
                        <div class="input-group">
                            <span class="input-group-text">Desde</span>
                            <input type="date" id="start-date-{{ $graphType }}"
                                   class="form-control form-control-sm"
                                   value="{{ date('Y-m-d', strtotime('-7 days')) }}">
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">Hasta</span>
                            <input type="date" id="end-date-{{ $graphType }}"
                                   class="form-control form-control-sm"
                                   value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <!-- Export Format Selection -->
                <div class="mb-3">
                    <label class="form-label">Formato de Exportación</label>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="exportGraph('{{ $graphType }}', 'pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button class="btn btn-outline-primary" onclick="exportGraph('{{ $graphType }}', 'xlsx')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button class="btn btn-outline-primary" onclick="exportGraph('{{ $graphType }}', 'csv')">
                            <i class="fas fa-file-csv"></i> CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
