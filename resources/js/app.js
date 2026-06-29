import './bootstrap';
import '../css/app.css';
import { initTheme } from './theme';

import Alpine from 'alpinejs';

initTheme();

window.Alpine = Alpine;

Alpine.start();
