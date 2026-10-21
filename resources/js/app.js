import './bootstrap';
import '../css/app.css';
import { initTheme } from './theme';
import './select2';
import { registerAlpineComponents } from './alpine-components';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

initTheme();

window.Alpine = Alpine;
Alpine.plugin(collapse);
registerAlpineComponents(Alpine);

Alpine.start();
