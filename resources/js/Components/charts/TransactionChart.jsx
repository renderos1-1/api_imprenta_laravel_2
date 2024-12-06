import React, { useState, useEffect } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const TransactionChart = () => {
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
            console.log('Fetching data with date range:', dateRange);

            const response = await fetch('/api/chart-data/transactions', {
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
            console.log('Received data:', jsonData);

            // Format the data to ensure all numbers are properly parsed
            const formattedData = jsonData.map(item => ({
                ...item,
                total: parseFloat(item.total)
            }));

            setData(formattedData);
        } catch (err) {
            setError(err.message);
            console.error('Error fetching data:', err);
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        console.log('Date range changed, fetching new data');
        fetchData();
    }, [dateRange]);

    // Auto-refresh every minute
    useEffect(() => {
        const interval = setInterval(fetchData, 60000);
        return () => clearInterval(interval);
    }, [dateRange]);

    const handleDateChange = (e) => {
        const { name, value } = e.target;
        console.log('Date change event:', name, value);
        setDateRange(prev => {
            const newRange = {
                ...prev,
                [name]: value
            };
            console.log('New date range:', newRange);
            return newRange;
        });
    };

    const formatCurrency = (value) => {
        return new Intl.NumberFormat('es-SV', {
            style: 'currency',
            currency: 'USD'
        }).format(value);
    };

    if (isLoading && !data.length) {
        return (
            <div className="flex items-center justify-center h-64">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900" />
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
                {isLoading && <span className="text-sm text-gray-500">Cargando...</span>}
            </div>

            <div className="h-[400px]">
                <ResponsiveContainer width="100%" height="100%">
                    <LineChart
                        data={data}
                        margin={{ top: 10, right: 30, left: 10, bottom: 20 }}
                    >
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis
                            dataKey="date"
                            tick={{ fontSize: 12 }}
                            angle={-45}
                            textAnchor="end"
                            height={50}
                        />
                        <YAxis
                            tick={{ fontSize: 12 }}
                            tickFormatter={formatCurrency}
                            label={{
                                value: 'Ingresos',
                                angle: -90,
                                position: 'insideLeft',
                                style: { textAnchor: 'middle' }
                            }}
                        />
                        <Tooltip
                            formatter={(value) => formatCurrency(value)}
                        />
                        <Legend />
                        <Line
                            type="monotone"
                            dataKey="total"
                            name="Ingresos"
                            stroke="#2563eb"
                            strokeWidth={2}
                            dot={{ r: 4 }}
                            activeDot={{ r: 8 }}
                        />
                    </LineChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
};

export default TransactionChart;
