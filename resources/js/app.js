import Alpine from 'alpinejs';

const THEMES = {
    emerald: '#13C878',
    midnight: '#5B8CFF',
    amber: '#F0A03C',
    mono: '#E8E8E8',
};

function applyTheme(name) {
    const accent = THEMES[name] || THEMES.emerald;
    const rgb = [1, 3, 5].map((i) => parseInt(accent.slice(i, i + 2), 16));
    const root = document.documentElement;
    root.style.setProperty('--color-accent', accent);
    root.style.setProperty('--color-accent-2', `color-mix(in srgb, ${accent} 80%, black)`);
    root.style.setProperty('--color-accent-soft', `rgba(${rgb.join(',')},0.15)`);
    localStorage.setItem('cardmaker-theme', name);
}

window.cardmakerTheme = applyTheme;
applyTheme(localStorage.getItem('cardmaker-theme') || 'emerald');

const MODES = { dark: 'dark', light: 'light' };

function applyMode(mode) {
    const normalized = MODES[mode] || 'dark';
    document.documentElement.classList.toggle('light', normalized === 'light');
    document.documentElement.style.colorScheme = normalized;
    localStorage.setItem('cardmaker-mode', normalized);
    window.dispatchEvent(new CustomEvent('cardmaker:mode', { detail: normalized }));
}

function currentMode() {
    return document.documentElement.classList.contains('light') ? 'light' : 'dark';
}

window.cardmakerMode = { apply: applyMode, current: currentMode };
applyMode(localStorage.getItem('cardmaker-mode') || 'dark');

window.Alpine = Alpine;
Alpine.start();