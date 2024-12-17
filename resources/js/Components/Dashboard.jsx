import React, { useState, useEffect } from 'react';
import { Card } from "@/components/ui/card";
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { Activity, FileText, TrendingUp } from "lucide-react";

const Dashboard = () => {
    const [stats, setStats] = useState({
        transactionsToday: { value: 0, change: 0 },
        processedDocs: { value: 0, change: 0 },
        revenue: { value: 0, change: 0 }
    });
    const [transactions, setTransactions] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const fetchData = async () => {
            try {
                setIsLoading(true);

                // Fetch stats
                const statsResponse = await fetch('/api/dashboard/stats');
                if (!statsResponse.ok) throw new Error('Failed to fetch stats');
                const statsData = await statsResponse.json();
                setStats(statsData);

                // Fetch transactions for chart
                const transactionsResponse = await fetch('/api/dashboard/transactions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({
                        start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
                        end_date: new Date().toISOString().split('T')[0]
                    })
                });
                if (!transactionsResponse.ok) throw new Error('Failed to fetch transactions');
                const transactionsData = await transactionsResponse.json();
                setTransactions(transactionsData);
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            } finally {
                setIsLoading(false);
            }
        };

        fetchData();
        // Auto-refresh every minute
        const interval = setInterval(fetchData, 60000);
        return () => clearInterval(interval);
    }, []);

    const StatCard = ({ title, value, change, icon: Icon }) => (
        <Card className="p-6">
            <div className="flex items-center justify-between">
                <div className="space-y-2">
                    <p className="text-sm font-medium text-gray-500">{title}</p>
                    <p className="text-2xl font-bold">
                        {title === 'Ingresos Hoy' ? '$' : ''}{value.toLocaleString()}
                    </p>
                    {change !== undefined && (
                        <p className={`text-sm ${change >= 0 ? 'text-green-500' : 'text-red-500'}`}>
                            {change >= 0 ? '+' : ''}{change}%
                            <span className="text-gray-500 ml-1">vs ayer</span>
                        </p>
                    )}
                </div>
                <div className="p-3 bg-blue-50 rounded-full">
                    <Icon className="h-6 w-6 text-blue-500" />
                </div>
            </div>
        </Card>
    );

    if (isLoading) {
        return <div className="flex items-center justify-center h-64">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900" />
        </div>;
    }

    return (
        <div className="bg-white rounded-lg shadow-sm m-4 p-6 mt-32">
            <div className="max-w-7xl mx-auto space-y-6">
                <h2 className="text-2xl font-bold">Buenos días, Admin</h2>
                <p className="text-gray-500">Aquí está el resumen de hoy</p>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <StatCard
                        title="Transacciones Hoy"
                        value={stats.transactionsToday.value}
                        change={stats.transactionsToday.change}
                        icon={Activity}
                    />
                    <StatCard
                        title="Documentos Procesados"
                        value={stats.processedDocs.value}
                        change={stats.processedDocs.change}
                        icon={FileText}
                    />
                    <StatCard
                        title="Ingresos Hoy"
                        value={stats.revenue.value}
                        change={stats.revenue.change}
                        icon={TrendingUp}
                    />
                </div>

                <Card className="p-6">
                    <div className="mb-4">
                        <h3 className="text-lg font-semibold">Transacciones Diarias</h3>
                    </div>
                    <div className="h-[300px]">
                        <ResponsiveContainer width="100%" height="100%">
                            <LineChart data={transactions}>
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis
                                    dataKey="date"
                                    tick={{ fontSize: 12 }}
                                />
                                <YAxis
                                    tick={{ fontSize: 12 }}
                                />
                                <Tooltip />
                                <Line
                                    type="monotone"
                                    dataKey="total"
                                    stroke="#3b82f6"
                                    strokeWidth={2}
                                    dot={{ r: 4 }}
                                    activeDot={{ r: 8 }}
                                    name="Transacciones"
                                />
                            </LineChart>
                        </ResponsiveContainer>
                    </div>
                </Card>
            </div>
        </div>
    );
};
//test
export default Dashboard;