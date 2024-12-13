import React, { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, Cell } from 'recharts';
import { Alert } from "@/components/ui/alert";
import ExportButton from '../ui/ExportButton';

const DepartmentsChart = () => {
    const [data, setData] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [activeBar, setActiveBar] = useState(null);
    const [dateRange, setDateRange] = useState({
        start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        end_date: new Date().toISOString().split('T')[0]
    });

    const fetchData = async () => {
        try {
            setIsLoading(true);
            const response = await fetch('/api/chart-data/department', {
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
                        Total Trámites: {payload[0].value}
                    </p>
                </div>
            );
        }
        return null;
    };

    if (isLoading && !data.length) {
        return (
            <div className="flex items-center justify-center h-full">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900" />
            </div>
        );
    }

    if (error) {
        return (
            <Alert variant="destructive">{error}</Alert>
        );
    }

    if (!data.length) {
        return (
            <div className="flex items-center justify-center h-full">
                <p className="text-gray-500">No hay datos disponibles</p>
            </div>
        );
    }

    return (
        <div className="w-full h-full">
            <div className="mb-4 flex justify-between">
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
                    chartType="department"
                    startDate={dateRange.start_date}
                    endDate={dateRange.end_date}
                />
            </div>

            <div style={{ width: '100%', height: 'calc(100% - 60px)' }}>
                <ResponsiveContainer>
                    <BarChart
                        data={data}
                        margin={{ top: 20, right: 30, left: 40, bottom: 60 }}
                    >
                        <XAxis
                            dataKey="name"
                            angle={-45}
                            textAnchor="end"
                            height={60}
                            tick={{ fontSize: 12 }}
                        />
                        <YAxis />
                        <Tooltip content={<CustomTooltip />} />
                        <Bar
                            dataKey="total"
                            fill="#4f46e5"
                            onMouseEnter={(_, index) => setActiveBar(index)}
                            onMouseLeave={() => setActiveBar(null)}
                        >
                            {data.map((entry, index) => (
                                <Cell
                                    key={`cell-${index}`}
                                    fill={index === activeBar ? '#3730a3' : '#4f46e5'}
                                />
                            ))}
                        </Bar>
                    </BarChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
};

export default DepartmentsChart;
