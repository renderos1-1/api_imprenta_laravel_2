import React, { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid } from 'recharts';

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
            return (
                <div className="bg-white p-4 shadow-lg rounded-lg border">
                    <p className="font-semibold text-gray-900">{label}</p>
                    <p className="text-gray-600">
                        Duración Promedio: {payload[0].value.toFixed(2)} minutos
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
            <div className="text-red-500 p-4 text-center">
                {error}
            </div>
        );
    }

    return (
        <div className="w-full">
            <div className="mb-4 flex gap-4">
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

            <div className="h-[600px]"> {/* Increased height for better visibility */}
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart
                        data={data}
                        layout="vertical" // This makes the bars horizontal
                        margin={{ top: 20, right: 30, left: 200, bottom: 20 }} // Increased left margin for labels
                    >
                        <CartesianGrid strokeDasharray="3 3" horizontal={false} />
                        <XAxis
                            type="number"
                            label={{
                                value: 'Duración Promedio (minutos)',
                                position: 'bottom',
                                offset: 0
                            }}
                        />
                        <YAxis
                            dataKey="name"
                            type="category"
                            width={180} // Fixed width for stage names
                            tick={{ fontSize: 12 }}
                        />
                        <Tooltip
                            content={({ active, payload, label }) => {
                                if (active && payload && payload.length) {
                                    return (
                                        <div className="bg-white p-4 shadow-lg rounded-lg border">
                                            <p className="font-semibold text-gray-900">{label}</p>
                                            <p className="text-gray-600">
                                                Duración Promedio: {payload[0].value.toFixed(2)} minutos
                                            </p>
                                        </div>
                                    );
                                }
                                return null;
                            }}
                        />
                        <Bar
                            dataKey="duration"
                            fill="#4f46e5"
                            name="Duración Promedio"
                            radius={[0, 4, 4, 0]} // Rounded corners on the right side
                        />
                    </BarChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
};

export default StageDurationChart;
