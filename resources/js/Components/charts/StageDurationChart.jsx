import React, { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid } from 'recharts';
import { Alert } from "@/components/ui/alert";
import ExportButton from '../ui/ExportButton';

const StageDurationChart = () => {
    const [data, setData] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [dateRange, setDateRange] = useState({
        start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        end_date: new Date().toISOString().split('T')[0]
    });

    const fetchData = async () => {
        try {
            setIsLoading(true);
            const response = await fetch('/api/chart-data/stage-duration', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(dateRange)
            });

            if (!response.ok) {
                throw new Error('Error al cargar los datos');
            }

            const jsonData = await response.json();
            setData(jsonData);
        } catch (err) {
            setError(err.message);
            console.error('Error fetching data:', err);
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
        // Set up auto-refresh interval
        const interval = setInterval(fetchData, 60000); // Refresh every minute
        return () => clearInterval(interval);
    }, [dateRange]);

    const handleDateChange = (e) => {
        const { name, value } = e.target;
        setDateRange(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const CustomTooltip = ({ active, payload, label }) => {
        if (active && payload && payload.length) {
            const duration = payload[0].value;
            const hours = Math.floor(duration / 60);
            const minutes = Math.round(duration % 60);

            return (
                <div className="bg-white p-4 shadow-lg rounded-lg border">
                    <p className="font-semibold text-gray-900">{label}</p>
                    <p className="text-gray-600">
                        Duración Promedio: {hours > 0 ? `${hours}h ` : ''}{minutes}m
                    </p>
                </div>
            );
        }
        return null;
    };

    if (isLoading && !data.length) {
        return (
            <div className="flex items-center justify-center h-64">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900" />
            </div>
        );
    }

    if (error) {
        return (
            <Alert variant="destructive" className="mb-4">
                {error}
            </Alert>
        );
    }

    if (!data.length) {
        return (
            <div className="flex items-center justify-center h-64">
                <p className="text-gray-500">No hay datos disponibles para el período seleccionado</p>
            </div>
        );
    }

    return (
        <div className="w-full space-y-4">
            <div className="flex justify-between items-center">
                <div className="flex gap-4">
                    <div className="flex items-center gap-2">
                        <label className="text-sm font-medium">Desde:</label>
                        <input
                            type="date"
                            name="start_date"
                            value={dateRange.start_date}
                            onChange={handleDateChange}
                            className="border rounded px-2 py-1 text-sm"
                        />
                    </div>
                    <div className="flex items-center gap-2">
                        <label className="text-sm font-medium">Hasta:</label>
                        <input
                            type="date"
                            name="end_date"
                            value={dateRange.end_date}
                            onChange={handleDateChange}
                            className="border rounded px-2 py-1 text-sm"
                        />
                    </div>
                </div>
                <ExportButton
                    chartType="stage-duration"
                    startDate={dateRange.start_date}
                    endDate={dateRange.end_date}
                />
            </div>

            <div className="h-[600px] w-full">
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart
                        data={data}
                        layout="vertical"
                        margin={{ top: 20, right: 30, left: 220, bottom: 40 }}
                    >
                        <CartesianGrid
                            strokeDasharray="3 3"
                            horizontal={false}
                            stroke="#E5E7EB"
                        />
                        <XAxis
                            type="number"
                            tickFormatter={(value) => {
                                const hours = Math.floor(value / 60);
                                const minutes = Math.round(value % 60);
                                return hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
                            }}
                            label={{
                                value: 'Duración Promedio',
                                position: 'bottom',
                                offset: 0
                            }}
                        />
                        <YAxis
                            dataKey="name"
                            type="category"
                            width={200}
                            tick={{
                                fontSize: 12,
                                fill: '#4B5563',
                                textAnchor: 'end'
                            }}
                        />
                        <Tooltip content={<CustomTooltip />} />
                        <Bar
                            dataKey="duration"
                            fill="#4f46e5"
                            name="Duración Promedio"
                            radius={[0, 4, 4, 0]}
                        />
                    </BarChart>
                </ResponsiveContainer>
            </div>

            <div className="text-sm text-gray-500 text-center">
                * Los tiempos mostrados son promedios basados en las transacciones completadas
            </div>
        </div>
    );
};

export default StageDurationChart;
