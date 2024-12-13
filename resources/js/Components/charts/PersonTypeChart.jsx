import React, { useState, useEffect } from 'react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip, Legend } from 'recharts';
import ExportButton from '../ui/ExportButton'; // Updated import

const PersonTypeChart = () => {
    const [data, setData] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [dateRange, setDateRange] = useState({
        start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        end_date: new Date().toISOString().split('T')[0]
    });

    // Colors for the pie chart segments
    const COLORS = ['#2563eb', '#7c3aed'];

    const fetchData = async () => {
        try {
            setIsLoading(true);
            const response = await fetch('/api/chart-data/person-type', {
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
                const errorData = await response.json();
                throw new Error(errorData.message || 'Error retrieving data');
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

        // Set up polling for real-time updates
        const interval = setInterval(fetchData, 60000); // Poll every minute
        return () => clearInterval(interval);
    }, [dateRange]);

    const handleDateChange = (e) => {
        const { name, value } = e.target;
        setDateRange(prev => ({
            ...prev,
            [name]: value
        }));
    };

    // Custom tooltip content
    const CustomTooltip = ({ active, payload }) => {
        if (active && payload && payload.length) {
            const data = payload[0].payload;
            return (
                <div className="bg-white p-4 shadow-lg rounded-lg border">
                    <p className="font-semibold">{data.display_name}</p>
                    <p>Total: {data.total}</p>
                    <p>Porcentaje: {data.percentage}%</p>
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
            <div className="flex justify-between items-center mb-4">
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
                    {isLoading && <span className="text-sm text-gray-500">Cargando...</span>}
                </div>

                <ExportButton
                    chartType="person-type"
                    startDate={dateRange.start_date}
                    endDate={dateRange.end_date}
                />
            </div>

            <div className="h-[400px]">
                <ResponsiveContainer width="100%" height="100%">
                    <PieChart>
                        <Pie
                            data={data}
                            dataKey="total"
                            nameKey="display_name"
                            cx="50%"
                            cy="50%"
                            outerRadius={150}
                            label={({ name, percent }) => `${name} (${(percent * 100).toFixed(1)}%)`}
                        >
                            {data.map((entry, index) => (
                                <Cell
                                    key={`cell-${index}`}
                                    fill={COLORS[index % COLORS.length]}
                                />
                            ))}
                        </Pie>
                        <Tooltip content={<CustomTooltip />} />
                        <Legend />
                    </PieChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
};

export default PersonTypeChart;
