/**
 * Safehouse Aurora theme preference: dark | light | system.
 * Resolves to html[data-theme="dark"|"light"] for CSS tokens.
 */
const STORAGE_KEY = 'safehouse.theme';
const PREFS = ['dark', 'light', 'system'];

function systemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function getThemePreference() {
    const stored = localStorage.getItem(STORAGE_KEY);

    return PREFS.includes(stored) ? stored : 'dark';
}

export function resolveTheme(preference = getThemePreference()) {
    return preference === 'system' ? systemTheme() : preference;
}

export function applyTheme(preference = getThemePreference()) {
    const resolved = resolveTheme(preference);
    const root = document.documentElement;

    root.setAttribute('data-theme', resolved);
    root.setAttribute('data-theme-pref', preference);
    root.style.colorScheme = resolved;

    return resolved;
}

export function setThemePreference(preference) {
    const next = PREFS.includes(preference) ? preference : 'dark';
    localStorage.setItem(STORAGE_KEY, next);
    applyTheme(next);
    syncSwitcherUi(next);

    return next;
}

function syncSwitcherUi(preference = getThemePreference()) {
    document.querySelectorAll('[data-theme-option]').forEach((el) => {
        const value = el.getAttribute('data-theme-option');
        const checked = value === preference;
        el.setAttribute('aria-checked', checked ? 'true' : 'false');
    });

    document.querySelectorAll('[data-theme-label]').forEach((el) => {
        const labels = {
            dark: el.getAttribute('data-label-dark') || 'Dark',
            light: el.getAttribute('data-label-light') || 'Light',
            system: el.getAttribute('data-label-system') || 'System',
        };
        el.textContent = labels[preference] || labels.dark;
    });
}

export function initThemeSwitcher() {
    applyTheme();
    syncSwitcherUi();

    document.querySelectorAll('[data-theme-option]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            const value = el.getAttribute('data-theme-option');
            if (value) {
                setThemePreference(value);
            }

            const details = el.closest('details');
            if (details) {
                details.open = false;
            }
        });
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (getThemePreference() === 'system') {
            applyTheme('system');
        }
    });
}
