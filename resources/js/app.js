import './bootstrap';
import '../css/app.css';
import { initTheme } from './theme';
import './select2';
import { registerAlpineComponents } from './alpine-components';

import Alpine from 'alpinejs';

initTheme();

window.Alpine = Alpine;
registerAlpineComponents(Alpine);

Alpine.start();
