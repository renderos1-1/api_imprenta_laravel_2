// Chart configuration and initialization
class StatisticsCharts {
    constructor() {
        this.chartInstances = {};
        this.chartConfigs = {
            revenue: {
                type: 'line',
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
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
            },
            personType: {
                type: 'pie',
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: this.createPercentageLabel
                            }
                        }
                    }
                }
            },
            documentType: {
                type: 'pie',
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: this.createPercentageLabel
                            }
                        }
                    }
                }
            }
        };

        this.colorSchemes = {
            revenue: {
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14, 165, 233, 0.1)'
            },
            pie: {
                backgroundColor: ['#1D3557', '#457B9D', '#A8DADC'],
                borderColor: ['black', 'black', 'black']
            }
        };
    }

    initialize(initialData) {
        this.initializeRevenueChart(initialData.revenue);
        this.initializePersonTypeChart(initialData.personType);
        this.initializeDocumentTypeChart(initialData.documentType);
    }

    initializeRevenueChart(data) {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        this.chartInstances.revenueChart = new Chart(ctx, {
            type: this.chartConfigs.revenue.type,
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Ingresos Diarios',
                    data: data.values,
                    ...this.colorSchemes.revenue,
                    tension: 0.4,
                    fill: true,
                    pointStyle: 'circle',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: this.chartConfigs.revenue.options
        });
    }

    initializePersonTypeChart(data) {
        const ctx = document.getElementById('personTypeChart');
        this.chartInstances.personTypeChart = new Chart(ctx, {
            type: this.chartConfigs.personType.type,
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    ...this.colorSchemes.pie,
                    borderWidth: 2
                }]
            },
            options: this.chartConfigs.personType.options
        });
    }

    initializeDocumentTypeChart(data) {
        const ctx = document.getElementById('documentTypeChart');
        this.chartInstances.documentTypeChart = new Chart(ctx, {
            type: this.chartConfigs.documentType.type,
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    ...this.colorSchemes.pie,
                    borderWidth: 2
                }]
            },
            options: this.chartConfigs.documentType.options
        });
    }

    createPercentageLabel(context) {
        const label = context.label || '';
        const value = context.raw || 0;
        const percentage = context.chart.data.percentages[context.dataIndex];
        return `${label}: ${value} (${percentage}%)`;
    }

    async updateCharts(startDate, endDate) {
        try {
            const response = await this.fetchChartData(startDate, endDate);
            if (!response.ok) throw new Error('Failed to fetch chart data');

            const data = await response.json();
            this.updateChartData(data);
        } catch (error) {
            console.error('Error updating graphs:', error);
            alert('Error al actualizar los gráficos. Por favor, intente nuevamente.');
        }
    }

    async fetchChartData(startDate, endDate) {
        return fetch('/estadisticas/api/chart-data/all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ start_date: startDate, end_date: endDate })
        });
    }

    updateChartData(data) {
        if (this.chartInstances.revenueChart) {
            this.chartInstances.revenueChart.data.labels = data.revenue.labels;
            this.chartInstances.revenueChart.data.datasets[0].data = data.revenue.values;
            this.chartInstances.revenueChart.update();
        }

        if (this.chartInstances.personTypeChart) {
            this.chartInstances.personTypeChart.data.labels = data.personType.labels;
            this.chartInstances.personTypeChart.data.datasets[0].data = data.personType.values;
            this.chartInstances.personTypeChart.update();
        }

        if (this.chartInstances.documentTypeChart) {
            this.chartInstances.documentTypeChart.data.labels = data.documentType.labels;
            this.chartInstances.documentTypeChart.data.datasets[0].data = data.documentType.values;
            this.chartInstances.documentTypeChart.update();
        }
    }
}

// Export functionality
class StatisticsExport {
    static async exportChart(type, format) {
        const startDate = document.getElementById('global-start-date').value;
        const endDate = document.getElementById('global-end-date').value;

        try {
            const response = await fetch(`/estadisticas/export/${type}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    format,
                    start_date: startDate,
                    end_date: endDate
                })
            });

            if (!response.ok) throw new Error('Export failed');

            const filename = `${type}_report_${startDate}_to_${endDate}.${format}`;
            await this.handleExportResponse(response, format, filename);

            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById(`exportModal-${type}`));
            if (modal) modal.hide();

        } catch (error) {
            console.error('Error exporting graph:', error);
            alert('Error al exportar. Por favor, intente nuevamente.');
        }
    }

    static async handleExportResponse(response, format, filename) {
        if (format === 'pdf') {
            const blob = await response.blob();
            this.downloadFile(blob, filename, 'application/pdf');
        } else {
            const data = await response.text();
            const blob = new Blob([data], {
                type: format === 'xlsx'
                    ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    : 'text/csv'
            });
            this.downloadFile(blob, filename);
        }
    }

    static downloadFile(blob, filename, type = null) {
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
}
