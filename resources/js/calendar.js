import { Calendar } from '@fullcalendar/core';
import idLocale from '@fullcalendar/core/locales/id';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('consultation-calendar');
    if (!el) return;

    const dataEl = document.getElementById('consultation-events');
    let events = [];
    if (dataEl) {
        try {
            events = JSON.parse(dataEl.textContent);
        } catch (error) {
            events = [];
        }
    }

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
        initialView: 'dayGridMonth',
        locale: idLocale,
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek',
        },
        events,
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
    });

    calendar.render();
});