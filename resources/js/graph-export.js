// resources/js/graph-export.js

// Store chart instances globally
const chartInstances = {};

/**
 * Initialize a graph with the given configuration
 */
function initializeGraph(type, chartId, data, config) {
    const canvas = document.getElementById(chartId);
    if (!canvas) {
        console.error(`Canvas with id ${chartId} not found`);
        return;
    }

    if (chartInstances[chartId]) {
        chartInstances[chartId].destroy();
    }

    try {
        const ctx = canvas.getContext('2d');
        const chartConfig = {
            ...config,
            data: data,
            options: {
                ...config.options,
                maintainAspectRatio: false,
                responsive: true,
                animation: {
                    duration: 750,
                    easing: 'easeInOutQuart'
                }
            }
        };

        chartInstances[chartId] = new Chart(ctx, chartConfig);
        return chartInstances[chartId];
    } catch (error) {
        console.error('Error initializing chart:', error);
    }
}

/**
 * Update all graphs based on the selected date range
 */
async function updateGraphs(startDate, endDate) {
    try {
        const response = await fetch('/estadisticas/api/chart-data/all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ start_date: startDate, end_date: endDate })
        });

        if (!response.ok) throw new Error('Failed to fetch chart data');

        const data = await response.json();

        // Update each chart with new data
        Object.entries(data).forEach(([type, chartData]) => {
            const chartId = `${type}-chart`;
            if (chartInstances[chartId]) {
                updateChartData(chartId, chartData);
            }
        });

    } catch (error) {
        console.error('Error updating graphs:', error);
        alert('Failed to update graphs. Please try again.');
    }
}

/**
 * Open the export modal for a specific chart
 */
function openExportModal(type) {
    const modal = new bootstrap.Modal(document.getElementById(`exportModal-${type}`));
    modal.show();
}

/**
 * Export graph data
 */
async function exportGraph(type, format) {
    try {
        const startDate = document.getElementById(`start-date-${type}`).value;
        const endDate = document.getElementById(`end-date-${type}`).value;

        const response = await fetch(`/estadisticas/export/${type}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                format: format,
                start_date: startDate,
                end_date: endDate
            })
        });

        if (!response.ok) throw new Error('Export failed');

        const filename = `${type}_report_${startDate}_to_${endDate}.${format}`;

        if (format === 'pdf') {
            const blob = await response.blob();
            downloadFile(blob, filename, 'application/pdf');
        } else {
            const data = await response.text();
            const blob = new Blob([data], {
                type: format === 'xlsx'
                    ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    : 'text/csv'
            });
            downloadFile(blob, filename);
        }

        // Close the modal after successful export
        const modal = bootstrap.Modal.getInstance(document.getElementById(`exportModal-${type}`));
        if (modal) modal.hide();

    } catch (error) {
        console.error('Error exporting graph:', error);
        alert('Failed to export graph. Please try again.');
    }
}

/**
 * Helper function to download a file
 */
function downloadFile(blob, filename, type = null) {
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.style.display = 'none';
    a.href = url;
    a.download = filename;

    if (type) {
        a.type = type;
    }

    document.body.appendChild(a);
    a.click();

    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
}
