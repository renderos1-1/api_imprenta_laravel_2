import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import Statistics from './Components/Statistics';

const container = document.getElementById('app');
if (container) {
    const root = createRoot(container);
    root.render(
        <React.StrictMode>
            <Statistics />
        </React.StrictMode>
    );
}
