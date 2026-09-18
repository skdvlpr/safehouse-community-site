import { readCookieConsent } from './cookie-consent';

const CONVERSION_EVENTS = new Set([
    'donate_success',
    'donate_recurring_success',
    'volunteer_success',
    'contact_success',
]);

const PII_QUERY_KEYS = ['donor_name', 'phone', 'email'];

const DENIED_CONSENT = {
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied',
    analytics_storage: 'denied',
};

let gtmInjected = false;
let conversionPushed = false;

function ensureGtag() {
    window.dataLayer = window.dataLayer || [];

    if (typeof window.gtag !== 'function') {
        window.gtag = function gtag() {
            window.dataLayer.push(arguments);
        };
    }
}

function bootNode() {
    return document.getElementById('measurement-boot');
}

function isBootable() {
    const node = bootNode();

    if (!node || node.dataset.measurementEnabled !== 'true') {
        return false;
    }

    return /^GTM-[A-Z0-9]+$/.test((node.dataset.measurementContainer || '').toUpperCase());
}

function containerId() {
    return (bootNode()?.dataset.measurementContainer || '').toUpperCase();
}

function stripPiiFromLocation() {
    const url = new URL(window.location.href);
    let changed = false;

    for (const key of PII_QUERY_KEYS) {
        if (url.searchParams.has(key)) {
            url.searchParams.delete(key);
            changed = true;
        }
    }

    if (changed) {
        window.history.replaceState(window.history.state, '', `${url.pathname}${url.search}${url.hash}`);
    }
}

function injectGtm(id) {
    if (gtmInjected) {
        return;
    }

    gtmInjected = true;

    // Official web container pattern (no noscript iframe).
    // https://developers.google.com/tag-platform/tag-manager/web
    (function (w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
        const f = d.getElementsByTagName(s)[0];
        const j = d.createElement(s);
        const dl = l !== 'dataLayer' ? `&l=${l}` : '';
        j.async = true;
        j.src = `https://www.googletagmanager.com/gtm.js?id=${i}${dl}`;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', id);
}

function pushConversion() {
    if (conversionPushed || readCookieConsent() !== 'all' || !isBootable() || !gtmInjected) {
        return;
    }

    const marker = document.querySelector('[data-measurement-event]');
    const name = marker?.getAttribute('data-measurement-event');

    if (!name || !CONVERSION_EVENTS.has(name)) {
        return;
    }

    conversionPushed = true;
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ event: name });
}

function startMeasurement() {
    if (!isBootable() || readCookieConsent() !== 'all') {
        return;
    }

    stripPiiFromLocation();
    ensureGtag();
    // Basic consent: defaults denied, then grant analytics only, then load GTM.
    // https://developers.google.com/tag-platform/security/guides/consent
    window.gtag('consent', 'default', {
        ...DENIED_CONSENT,
        wait_for_update: 500,
    });
    window.gtag('consent', 'update', {
        analytics_storage: 'granted',
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied',
    });
    injectGtm(containerId());
    pushConversion();
}

function denyMeasurement() {
    ensureGtag();
    window.gtag('consent', 'update', { ...DENIED_CONSENT });
}

export function initMeasurement() {
    const run = () => {
        if (readCookieConsent() === 'all') {
            startMeasurement();
        }

        document.addEventListener('cookie-consent:changed', (event) => {
            const level = event.detail?.level;

            if (level === 'all') {
                startMeasurement();

                return;
            }

            if (level === 'essential') {
                denyMeasurement();
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run, { once: true });
    } else {
        run();
    }
}
