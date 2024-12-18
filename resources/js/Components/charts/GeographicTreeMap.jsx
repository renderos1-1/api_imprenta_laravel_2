import React, { useState, useEffect } from 'react';
import { Treemap, ResponsiveContainer, Tooltip } from 'recharts';
import { scaleLinear } from 'd3-scale';
import { Card } from '../ui/card';

const CustomTooltip = ({ active, payload }) => {
    if (!active || !payload || !payload[0]) return null;

    const data = payload[0].payload;
    return (
        <div className="bg-white p-4 rounded-lg shadow-lg border">
            <h3 className="font-bold text-lg mb-2">{data.name}</h3>
            <p className="text-sm text-gray-600 mb-2">Total Trámites: {data.value}</p>

            {data.municipalities && data.municipalities.length > 0 && (
                <div className="mt-2">
                    <p className="font-semibold mb-1">Municipios:</p>
                    <div className="max-h-48 overflow-y-auto">
                        {data.municipalities.map((muni) => (
                            <div key={muni.city_code} className="flex justify-between text-sm py-1">
                                <span>{muni.city_name}</span>
                                <span className="font-medium">{muni.transactions}</span>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            <div className="mt-2 pt-2 border-t">
                <div className="flex items-center gap-2 text-sm">
                    <div className="w-3 h-3 rounded-full" style={{ backgroundColor: '#10B981' }}></div>
                    <span>Personal: {data.personal_transactions}</span>
                </div>
                <div className="flex items-center gap-2 text-sm mt-1">
                    <div className="w-3 h-3 rounded-full" style={{ backgroundColor: '#6366F1' }}></div>
                    <span>Para Terceros: {data.third_party_transactions}</span>
                </div>
            </div>
        </div>
    );
};

const DepartmentTreeMap = () => {
    const [data, setData] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [dateRange, setDateRange] = useState({
        startDate: null,
        endDate: new Date().toISOString().split('T')[0]
    });

    useEffect(() => {
        const fetchData = async () => {
            try {
                setIsLoading(true);
                const response = await fetch('/api/chart-data/departments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify(dateRange)
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch data');
                }

                const responseData = await response.json();

                const transformedData = responseData.map(dept => ({
                    name: dept.state_name,
                    value: dept.total_transactions,
                    municipalities: dept.municipalities,
                    personal_transactions: dept.personal_transactions,
                    third_party_transactions: dept.third_party_transactions
                }));

                console.log('Transformed Data:', transformedData);

                setData([{
                    name: "",
                    children: transformedData
                }]);

            } catch (err) {
                setError(err.message);
                console.error('Error fetching data:', err);
            } finally {
                setIsLoading(false);
            }
        };

        fetchData();

        const interval = setInterval(fetchData, 60000);
        return () => clearInterval(interval);
    }, [dateRange]);

    const handleDateChange = (e) => {
        const { name, value } = e.target;
        setDateRange(prev => ({
            ...prev,
            [name]: value
        }));
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
            <div className="text-red-500 p-4">
                Error: {error}
            </div>
        );
    }

    console.log('Data passed to Treemap:', data);

    // Define a color scale
    const colorScale = scaleLinear()
        .domain([0, Math.max(...data[0].children.map(d => d.value))])
        .range(['#E0F7FA', '#01579B']); // Pale blue to navy blue

    return (
        <div className="w-full">
            <div className="mb-4 flex gap-4">
                <div className="flex items-center gap-2">
                    <label className="text-sm font-medium">Desde:</label>
                    <input
                        type="date"
                        name="startDate"
                        value={dateRange.startDate || ''}
                        onChange={handleDateChange}
                        className="border rounded px-2 py-1 text-sm"
                    />
                </div>
                <div className="flex items-center gap-2">
                    <label className="text-sm font-medium">Hasta:</label>
                    <input
                        type="date"
                        name="endDate"
                        value={dateRange.endDate}
                        onChange={handleDateChange}
                        className="border rounded px-2 py-1 text-sm"
                    />
                </div>
            </div>

            <div className="h-[700px]"> {/* Increased height */}
                <ResponsiveContainer width="100%" height="100%">
                    <Treemap
                        data={data}
                        dataKey="value"
                        stroke="#fff"
                        fill={d => colorScale(d.value)}
                        animationDuration={300}
                        padding={[10, 10, 10, 10]}
                    >
                        <Tooltip content={<CustomTooltip />} />
                    </Treemap>
                </ResponsiveContainer>
            </div>
        </div>
    );
};

export default DepartmentTreeMap;
