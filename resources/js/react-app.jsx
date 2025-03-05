import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import Statistics from './Components/Statistics';
import Dashboard from './Components/Dashboard';
import UserManagement from './Components/UserManagement/UserManagement';

const container = document.getElementById('app');
if (container) {
    const root = createRoot(container);
    root.render(
        <React.StrictMode>
            <Statistics />
        </React.StrictMode>
    );
}

const dashContainer = document.getElementById('dashboard-root');
if (dashContainer) {
    console.log('Found dashboard container');
    const root = createRoot(dashContainer);
    root.render(
        <React.StrictMode>
            <Dashboard />
        </React.StrictMode>
    );
} else {
    console.log('Dashboard container not found');
}

window.UserManagement = UserManagement;
