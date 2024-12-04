<!-- resources/views/components/date-range.blade.php -->
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
