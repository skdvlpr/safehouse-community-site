/**
 * Home impact counters: ease from 0 to the CRM total in about two seconds.
 *
 * Server HTML keeps the formatted final value for no-JS and PHPUnit.
 * https://developer.mozilla.org/en-US/docs/Web/API/Window/requestAnimationFrame
 * https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion
 */

const COUNT_DURATION_MS = 2000;

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

function formatCount(value) {
    return new Intl.NumberFormat('it-IT', { maximumFractionDigits: 0 }).format(value);
}

function easeOutCubic(t) {
    return 1 - ((1 - t) ** 3);
}

function animateCounter(el, target) {
    el.textContent = formatCount(0);

    const firstFrame = (startStamp) => {
        const step = (now) => {
            const elapsed = now - startStamp;
            const progress = Math.min(1, elapsed / COUNT_DURATION_MS);
            const current = Math.round(target * easeOutCubic(progress));

            el.textContent = formatCount(current);

            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                el.textContent = formatCount(target);
            }
        };

        window.requestAnimationFrame(step);
    };

    window.requestAnimationFrame(firstFrame);
}

export function initImpactCount() {
    if (reducedMotion.matches) {
        return;
    }

    document.querySelectorAll('[data-count-to]').forEach((el) => {
        if (!(el instanceof HTMLElement)) {
            return;
        }

        const raw = el.getAttribute('data-count-to');
        const target = raw === null ? Number.NaN : Number.parseInt(raw, 10);

        if (! Number.isFinite(target) || target < 1) {
            return;
        }

        animateCounter(el, target);
    });
}
