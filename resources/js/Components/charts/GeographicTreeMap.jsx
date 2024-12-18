import React, { useState, useEffect } from 'react';
import { Treemap, ResponsiveContainer, Tooltip } from 'recharts';
import { Card } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';

const COLORS = [
    '#4f46e5', // Indigo
    '#60a5fa', // Blue
    '#34d399', // Emerald
    '#fbbf24', // Amber
    '#f87171', // Red
    '#c084fc', // Purple
    '#94a3b8', // Slate
    '#2dd4bf', // Teal
];

const GeographicTreeMap = () => {
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
            const response = await fetch('/api/chart-data/geographic', {
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

    const CustomTooltip = ({ active, payload }) => {
        if (!active || !payload || !payload[0]) return null;

        const data = payload[0].payload;
        return (
            <div className="bg-white p-4 rounded shadow-lg border">
                <p className="font-semibold">{data.name}</p>
                <p>{data.type === 'department' ? 'Departamento' : 'Municipio'}</p>
                <p>Trámites: {data.value}</p>
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
        <Card className="p-6">
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

            <div className="h-[600px]">
                <ResponsiveContainer width="100%" height="100%">
                    <Treemap
                        data={data}
                        dataKey="value"
                        ratio={4/3}
                        stroke="#fff"
                        fill="#8884d8"
                        content={<CustomizedContent colors={COLORS} />}
                    >
                        <Tooltip content={<CustomTooltip />} />
                    </Treemap>
                </ResponsiveContainer>
            </div>
        </Card>
    );
};

// Custom content component for treemap cells
const CustomizedContent = ({ root, depth, x, y, width, height, index, colors, name, value }) => {
    const color = colors[Math.floor(Math.random() * colors.length)];

    return (
        <g>
            <rect
                x={x}
                y={y}
                width={width}
                height={height}
                fill={color}
                stroke="#fff"
                strokeWidth={2}
            />
            {width > 50 && height > 30 && (
                <text
                    x={x + width / 2}
                    y={y + height / 2}
                    textAnchor="middle"
                    fill="#fff"
                    fontSize={14}
                >
                    {name}
                </text>
            )}
        </g>
    );
};

export default GeographicTreeMap;
