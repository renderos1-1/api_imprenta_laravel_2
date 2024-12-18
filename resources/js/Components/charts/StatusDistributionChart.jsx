import React, { useState, useEffect } from 'react';
import {
    AreaChart,
    Area,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer
} from 'recharts';
import { Card } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';

const StatusDistributionChart = () => {
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
            const response = await fetch('/api/chart-data/status-distribution', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                credentials: 'include',
                body: JSON.stringify(dateRange)
            });

            if (!response.ok) {
                throw new Error('Error fetching data');
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
        if (!active || !payload) return null;

        return (
            <div className="bg-white p-3 rounded shadow-lg border">
                <p className="font-semibold">{new Date(label).toLocaleDateString('es-SV')}</p>
                {payload.map((entry, index) => (
                    <p key={index} style={{ color: entry.color }}>
                        {entry.name}: {entry.value}
                    </p>
                ))}
            </div>
        );
    };

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-64">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900" />
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

            <div className="h-[400px]">
                <ResponsiveContainer width="100%" height="100%">
                    <AreaChart
                        data={data}
                        margin={{ top: 10, right: 30, left: 0, bottom: 0 }}
                    >
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis
                            dataKey="date"
                            tickFormatter={(date) => new Date(date).toLocaleDateString('es-SV')}
                        />
                        <YAxis />
                        <Tooltip content={<CustomTooltip />} />
                        <Legend />
                        <Area
                            type="monotone"
                            dataKey="Pendientes"
                            stackId="1"
                            stroke="#fbbf24"
                            fill="#fbbf24"
                            fillOpacity={0.6}
                        />
                        <Area
                            type="monotone"
                            dataKey="Completados"
                            stackId="1"
                            stroke="#34d399"
                            fill="#34d399"
                            fillOpacity={0.6}
                        />
                    </AreaChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
};

export default StatusDistributionChart;
