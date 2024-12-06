import React from 'react';
import { Card } from '@/components/ui/card';
import RevenueChart from './charts/RevenueChart';
import PersonTypeChart from './charts/PersonTypeChart';
import DocumentTypeChart from './charts/DocumentTypeChart';
import DepartmentsChart from './charts/DepartmentsChart';
import { Button } from '@/components/ui/button';

const Statistics = () => {
    return (
        <div className="p-6 space-y-6">
            <h1 className="text-2xl font-bold mb-6">Estadísticas del Sistema</h1>

            {/* Revenue Chart - Full Width */}
            <Card className="p-6">
                <div className="flex justify-between items-center mb-4">
                    <h2 className="text-lg font-semibold">Ingresos en el Tiempo</h2>
                    <Button variant="outline" onClick={() => {}}>
                        Exportar
                    </Button>
                </div>
                <RevenueChart />
            </Card>

            {/* Person Type Distribution - Full Width */}
            <Card className="p-6">
                <div className="flex justify-between items-center mb-4">
                    <h2 className="text-lg font-semibold">Distribución por tipo de persona</h2>
                    <Button variant="outline" onClick={() => {}}>
                        Exportar
                    </Button>
                </div>
                <PersonTypeChart />
            </Card>

            {/* Grid Container for Two Half-Width Charts */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* Document Type Distribution */}
                <Card className="p-6">
                    <div className="flex justify-between items-center mb-4">
                        <h2 className="text-lg font-semibold">Distribución por tipo de documento</h2>
                        <Button variant="outline" onClick={() => {}}>
                            Exportar
                        </Button>
                    </div>
                    <div className="h-[300px]">
                        <DocumentTypeChart />
                    </div>
                </Card>

                {/* Placeholder for future chart */}
                <Card className="p-6">
                    <div className="flex justify-between items-center mb-4">
                        <h2 className="text-lg font-semibold">Chart Title 2</h2>
                        <Button variant="outline" onClick={() => {}}>
                            Exportar
                        </Button>
                    </div>
                    <div className="h-[300px]">
                        {/* Future chart will go here */}
                    </div>
                </Card>
            </div>

            {/* Departments Chart - Full Width with larger height */}
            <Card className="p-6">
                <div className="flex justify-between items-center mb-4">
                    <h2 className="text-lg font-semibold">Transacciones por Departamento</h2>
                    <Button variant="outline" onClick={() => {}}>
                        Exportar
                    </Button>
                </div>
                <div className="h-[500px]"> {/* Increased height */}
                    <DepartmentsChart />
                </div>
            </Card>
        </div>
    );
};

export default Statistics;
