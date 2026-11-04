const STORAGE_KEY = 'bk-theme';

export function getStoredTheme() {
    return localStorage.getItem(STORAGE_KEY);
}

export function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function getEffectiveTheme() {
    return getStoredTheme() || getSystemTheme();
}

export function applyTheme(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
}

export function setTheme(theme) {
    localStorage.setItem(STORAGE_KEY, theme);
    applyTheme(theme);
}

export function toggleTheme() {
    const next = getEffectiveTheme() === 'dark' ? 'light' : 'dark';
    setTheme(next);

    return next;
}

export function initTheme() {
    applyTheme(getEffectiveTheme());

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
        if (! getStoredTheme()) {
            applyTheme(event.matches ? 'dark' : 'light');
        }
    });
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('themeToggle', () => ({
        theme: getEffectiveTheme(),
        init() {
            this.theme = getEffectiveTheme();
        },
        toggle() {
            this.theme = toggleTheme();
        },
        isDark() {
            return this.theme === 'dark';
        },
    }));
});
