import React from 'react';
import { Card } from '@/components/ui/card';
import RevenueChart from './charts/RevenueChart';
import PersonTypeChart from './charts/PersonTypeChart';
import DocumentTypeChart from './charts/DocumentTypeChart';
import DepartmentsChart from './charts/DepartmentsChart';
import { Button } from '@/components/ui/button';
import StageDurationChart from "./charts/StageDurationChart.jsx";

const Statistics = () => {
    return (
        // Remove the outer padding and use flex to fill available space
        <div className="flex-1 w-full h-full overflow-auto">
            {/* Content container with proper spacing */}
            <div className="container mx-auto px-4 py-4 max-w-[1400px]">
                {/* Page header */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-800">Estadísticas del Sistema</h1>
                </div>

                {/* Charts grid with proper spacing */}
                <div className="space-y-6">
                    {/* Revenue Chart */}
                    <Card className="shadow-sm">
                        <div className="p-4 border-b">
                            <div className="flex justify-between items-center">
                                <h2 className="text-lg font-semibold text-gray-700">Ingresos en el Tiempo</h2>
                                <Button variant="outline" size="sm">
                                    Exportar
                                </Button>
                            </div>
                        </div>
                        <div className="p-4">
                            <RevenueChart />
                        </div>
                    </Card>

                    {/* Person Type Distribution */}
                    <Card className="shadow-sm">
                        <div className="p-4 border-b">
                            <div className="flex justify-between items-center">
                                <h2 className="text-lg font-semibold text-gray-700">Distribución por tipo de persona</h2>
                                <Button variant="outline" size="sm">
                                    Exportar
                                </Button>
                            </div>
                        </div>
                        <div className="p-4">
                            <PersonTypeChart />
                        </div>
                    </Card>

                    {/* Two column layout for smaller charts */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {/* Document Type Distribution */}
                        <Card className="shadow-sm">
                            <div className="p-4 border-b">
                                <div className="flex justify-between items-center">
                                    <h2 className="text-lg font-semibold text-gray-700">Distribución por tipo de documento</h2>
                                    <Button variant="outline" size="sm">
                                        Exportar
                                    </Button>
                                </div>
                            </div>
                            <div className="p-4 h-[300px]">
                                <DocumentTypeChart />
                            </div>
                        </Card>

                        {/* Placeholder chart */}
                        <Card className="shadow-sm">
                            <div className="p-4 border-b">
                                <div className="flex justify-between items-center">
                                    <h2 className="text-lg font-semibold text-gray-700">Chart Title 2</h2>
                                    <Button variant="outline" size="sm">
                                        Exportar
                                    </Button>
                                </div>
                            </div>
                            <div className="p-4 h-[300px]">
                                {/* Future chart */}
                            </div>
                        </Card>
                    </div>

                    {/* Departments Chart */}
                    <Card className="shadow-sm">
                        <div className="p-4 border-b">
                            <div className="flex justify-between items-center">
                                <h2 className="text-lg font-semibold text-gray-700">Transacciones por Departamento</h2>
                                <Button variant="outline" size="sm">
                                    Exportar
                                </Button>
                            </div>
                        </div>
                        <div className="p-4 h-[500px]">
                            <DepartmentsChart />
                        </div>
                    </Card>

                    {/* Stage Duration Chart */}
                    <Card className="shadow-sm">
                        <div className="p-4 border-b">
                            <div className="flex justify-between items-center">
                                <h2 className="text-lg font-semibold text-gray-700">Análisis de duración de etapas</h2>
                                <Button variant="outline" size="sm">
                                    Exportar
                                </Button>
                            </div>
                        </div>
                        <div className="p-4 overflow-x-auto">
                            <div className="min-w-[800px] h-[600px]">
                                <StageDurationChart />
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    );
};

export default Statistics;
