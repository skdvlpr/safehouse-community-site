const STORAGE_KEY = 'sh_cookie_consent';
const COOKIE_NAME = 'sh_cookie_consent';
const COOKIE_MAX_AGE = 60 * 60 * 24 * 365;

function readConsent() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);

        if (stored === 'essential' || stored === 'all') {
            return stored;
        }
    } catch {
        // localStorage unavailable
    }

    const match = document.cookie.match(new RegExp(`(?:^|; )${COOKIE_NAME}=([^;]*)`));

    if (match && (match[1] === 'essential' || match[1] === 'all')) {
        return match[1];
    }

    return null;
}

function persistConsent(level) {
    try {
        localStorage.setItem(STORAGE_KEY, level);
    } catch {
        // ignore
    }

    const secure = window.location.protocol === 'https:' ? '; Secure' : '';

    document.cookie = `${COOKIE_NAME}=${level}; Path=/; Max-Age=${COOKIE_MAX_AGE}; SameSite=Strict${secure}`;
}

function postConsent(level) {
    const banner = document.getElementById('cookie-consent-banner');

    if (!banner) {
        return;
    }

    const url = banner.dataset.storeUrl;

    if (!url) {
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            ...(token ? { 'X-CSRF-TOKEN': token } : {}),
        },
        body: JSON.stringify({ level }),
        credentials: 'same-origin',
    }).catch(() => {
        // Audit log is best-effort; client preference still applies.
    });
}

function setPreferencesOpen(banner, open) {
    const panel = document.getElementById('cookie-consent-panel');
    const toggle = banner.querySelector('[data-cookie-open-preferences]');

    panel?.classList.toggle('is-hidden', !open);
    panel?.setAttribute('aria-hidden', open ? 'false' : 'true');
    toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
}

function hideBanner() {
    const banner = document.getElementById('cookie-consent-banner');

    if (!banner) {
        return;
    }

    banner.classList.add('is-hidden');
    banner.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('cookie-banner-open');
    setPreferencesOpen(banner, false);
}

function applyConsent(level, { record = true } = {}) {
    persistConsent(level);
    hideBanner();

    if (record) {
        postConsent(level);
    }

    document.dispatchEvent(new CustomEvent('cookie-consent:changed', { detail: { level } }));
}

function bindCookieConsent() {
    const banner = document.getElementById('cookie-consent-banner');

    if (!banner || banner.dataset.bound === 'true') {
        return;
    }

    banner.dataset.bound = 'true';

    const existing = readConsent();

    if (existing) {
        hideBanner();

        return;
    }

    banner.classList.remove('is-hidden');
    banner.setAttribute('aria-hidden', 'false');
    document.body.classList.add('cookie-banner-open');

    banner.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        const acceptAll = target.closest('[data-cookie-accept-all]');
        const essentialOnly = target.closest('[data-cookie-essential-only]');
        const openPreferences = target.closest('[data-cookie-open-preferences]');
        const closePreferences = target.closest('[data-cookie-close-preferences]');
        const savePreferences = target.closest('[data-cookie-save-preferences]');

        if (acceptAll) {
            event.preventDefault();
            applyConsent('all');

            return;
        }

        if (essentialOnly) {
            event.preventDefault();
            applyConsent('essential');

            return;
        }

        if (openPreferences) {
            event.preventDefault();
            const panel = document.getElementById('cookie-consent-panel');
            const isOpen = panel ? !panel.classList.contains('is-hidden') : false;

            setPreferencesOpen(banner, !isOpen);

            return;
        }

        if (closePreferences) {
            event.preventDefault();
            setPreferencesOpen(banner, false);

            return;
        }

        if (savePreferences) {
            event.preventDefault();
            const analytics = banner.querySelector('[data-cookie-analytics]')?.checked ?? false;

            applyConsent(analytics ? 'all' : 'essential');
        }
    });
}

export function initCookieConsent() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindCookieConsent, { once: true });
    } else {
        bindCookieConsent();
    }
}
