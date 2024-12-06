import React, { useState, useEffect, useCallback } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { Alert, AlertDescription } from "@/components/ui/alert";

const RevenueChart = () => {
    const [data, setData] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [dateRange, setDateRange] = useState({
        start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        end_date: new Date().toISOString().split('T')[0]
    });

    const fetchData = useCallback(async () => {
        try {
            setIsLoading(true);
            setError(null);

            const response = await fetch('/api/chart-data/revenue', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify(dateRange)
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Error loading revenue data');
            }

            const jsonData = await response.json();

            // Ensure data is properly formatted
            const formattedData = jsonData.map(item => ({
                date: item.date,
                total: Number(item.total) || 0
            }));

            setData(formattedData);

        } catch (err) {
            console.error('Error fetching revenue data:', err);
            setError(err.message);
        } finally {
            setIsLoading(false);
        }
    }, [dateRange]);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    const handleDateChange = (e) => {
        const { name, value } = e.target;
        setDateRange(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const formatCurrency = (value) => {
        return new Intl.NumberFormat('es-SV', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2
        }).format(value);
    };

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-64">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
            </div>
        );
    }

    if (error) {
        return (
            <Alert variant="destructive">
                <AlertDescription>{error}</AlertDescription>
            </Alert>
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

            <div className="h-96">
                {data.length > 0 ? (
                    <ResponsiveContainer width="100%" height="100%">
                        <LineChart
                            data={data}
                            margin={{ top: 10, right: 30, left: 60, bottom: 40 }}
                        >
                            <CartesianGrid strokeDasharray="3 3" />
                            <XAxis
                                dataKey="date"
                                angle={-45}
                                textAnchor="end"
                                height={60}
                                tick={{ fontSize: 12 }}
                            />
                            <YAxis
                                tickFormatter={formatCurrency}
                                tick={{ fontSize: 12 }}
                            />
                            <Tooltip
                                formatter={(value) => formatCurrency(value)}
                                labelFormatter={(date) => new Date(date).toLocaleDateString('es-SV')}
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
                ) : (
                    <div className="flex items-center justify-center h-full">
                        <p className="text-gray-500">No hay datos para el rango seleccionado</p>
                    </div>
                )}
            </div>
        </div>
    );
};

export default RevenueChart;
