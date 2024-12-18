import React, { useState, useEffect } from 'react';
import { PieChart, Pie, Sector, ResponsiveContainer, Cell } from 'recharts';
import { Alert } from "../ui/alert";
import ExportButton from '../ui/ExportButton'; // Updated import

const DocumentTypeChart = () => {
    // Define colors for each document type
    const COLORS = {
        'dui': '#4f46e5',    // Indigo
        'passport': '#0ea5e9', // Sky blue
        'nit': '#8b5cf6'     // Purple
    };

    const [data, setData] = useState([]);
    const [activeIndex, setActiveIndex] = useState(0);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [dateRange, setDateRange] = useState({
        start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        end_date: new Date().toISOString().split('T')[0]
    });

    const fetchData = async () => {
        try {
            setIsLoading(true);
            const response = await fetch('/api/chart-data/document-type', {
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

    const renderActiveShape = (props) => {
        const {
            cx, cy, innerRadius, outerRadius, startAngle, endAngle,
            payload, value, percentage
        } = props;

        const color = COLORS[payload.document_type] || '#94a3b8';

        return (
            <g>
                <text x={cx} y={cy} dy={-20} textAnchor="middle" fill={color}>
                    {payload.name}
                </text>
                <text x={cx} y={cy} dy={10} textAnchor="middle" fill="#999">
                    {`${value} documentos`}
                </text>
                <text x={cx} y={cy} dy={30} textAnchor="middle" fill="#999">
                    {`(${percentage}%)`}
                </text>
                <Sector
                    cx={cx}
                    cy={cy}
                    innerRadius={innerRadius}
                    outerRadius={outerRadius}
                    startAngle={startAngle}
                    endAngle={endAngle}
                    fill={color}
                />
                <Sector
                    cx={cx}
                    cy={cy}
                    startAngle={startAngle}
                    endAngle={endAngle}
                    innerRadius={outerRadius + 6}
                    outerRadius={outerRadius + 10}
                    fill={color}
                />
            </g>
        );
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
            <Alert variant="destructive">{error}</Alert>
        );
    }

    return (
        <div className="w-full">
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
                    chartType="document-type"
                    startDate={dateRange.start_date}
                    endDate={dateRange.end_date}
                />
            </div>

            <div className="h-[300px]">
                <ResponsiveContainer width="100%" height="100%">
                    <PieChart>
                        <Pie
                            activeIndex={activeIndex}
                            activeShape={renderActiveShape}
                            data={data}
                            cx="50%"
                            cy="50%"
                            innerRadius={60}
                            outerRadius={80}
                            dataKey="value"
                            onMouseEnter={(_, index) => setActiveIndex(index)}
                        >
                            {data.map((entry, index) => (
                                <Cell
                                    key={`cell-${index}`}
                                    fill={COLORS[entry.document_type] || '#94a3b8'}
                                />
                            ))}
                        </Pie>
                    </PieChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
};

export default DocumentTypeChart;
