import React from 'react';
import { Card } from './ui/card';
import RevenueChart from './charts/RevenueChart';
import PersonTypeChart from './charts/PersonTypeChart';
import DocumentTypeChart from './charts/DocumentTypeChart';
import GeographicTreeMap from "./charts/GeographicTreeMap.jsx";
import { Button } from './ui/button';
import StatusDistributionChart from "./charts/StatusDistributionChart.jsx";
import DestinationTypeChart from "./charts/DestinationTypeChart.jsx";

const Statistics = () => {
    return (
        // Main container that takes full width and height
        <div className="flex flex-col w-full min-h-screen bg-gray-50">
            {/* Page header fixed at top */}


            {/* Scrollable content area */}
            <div className="flex-1 overflow-auto">
                <div className="container mx-auto px-6 py-6">
                    {/* Charts grid */}
                    <div className="space-y-6">
                        {/* Revenue Chart */}
                        <Card className="shadow-sm">
                            <div className="p-4 border-b">
                                <div className="flex justify-between items-center">
                                    <h2 className="text-lg font-semibold text-gray-700">Ingresos en el Tiempo</h2>

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
                                        <h2 className="text-lg font-semibold text-gray-700">Distribución por destinatario</h2>

                                    </div>
                                </div>
                                <div className="p-4 h-[300px]">
                                    <DestinationTypeChart/>
                                </div>
                            </Card>
                        </div>

                        {/* Departments Chart */}
                        <Card className="shadow-sm">
                            <div className="p-4 border-b">
                                <div className="flex justify-between items-center">
                                    <h2 className="text-lg font-semibold text-gray-700">Transacciones por Departamento</h2>

                                </div>
                            </div>
                            <div className="p-4 h-[1000px]">
                                <GeographicTreeMap />
                            </div>
                        </Card>

                        {/* Stage Duration Chart */}
                        <Card className="shadow-sm">
                            <div className="p-4 border-b">
                                <div className="flex justify-between items-center">
                                    <h2 className="text-lg font-semibold text-gray-700">Análisis de duración de etapas</h2>

                                </div>
                            </div>
                            <div className="p-4 overflow-x-auto">
                                <div className="min-w-[800px] h-[600px]">
                                    <StatusDistributionChart />
                                </div>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Statistics;
