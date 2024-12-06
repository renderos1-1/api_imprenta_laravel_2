import './bootstrap';
import './sidebar.js';
import './transacciones.js';
import Alpine from 'alpinejs';
import './cerrarsesion.js';
import axios from 'axios';

// Import React app
import('./react-app.jsx').catch(e => console.error('Error loading React app:', e));

window.Alpine = Alpine;
Alpine.start();

const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}
