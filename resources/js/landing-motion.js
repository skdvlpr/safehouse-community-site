/**
 * Membership landing motion.
 *
 * - Values ticker: CSS animation, constant speed (px/s) regardless of how many
 *   values the CMS provides; pause/resume control required by WCAG 2.2.2
 *   https://www.w3.org/WAI/WCAG22/Understanding/pause-stop-hide.html
 * - Card entrance: IntersectionObserver adds `.is-inview`; cards that enter in the
 *   same frame get a short stagger.
 *   https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API
 * - prefers-reduced-motion: ticker starts paused, nothing slides, all content shown.
 *   https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion
 */

const MARQUEE_SPEED_PX_PER_S = 110;
const MARQUEE_MIN_DURATION_S = 6;
const REVEAL_STAGGER_MS = 90;
const REVEAL_MAX_STAGGER_STEPS = 3;

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

function revealAll() {
    document.querySelectorAll('.landing-reveal').forEach((el) => {
        el.style.removeProperty('--reveal-delay');
        el.classList.add('is-inview');
    });
}

function setupMarquee(control) {
    if (!(control instanceof HTMLButtonElement)) {
        return;
    }

    const marquee = control.closest('.landing-marquee');

    if (!(marquee instanceof HTMLElement)) {
        return;
    }

    const pauseLabel = control.dataset.pauseLabel ?? control.textContent ?? '';
    const playLabel = control.dataset.playLabel ?? 'Play';

    const setPaused = (paused) => {
        marquee.classList.toggle('is-paused', paused);
        // `.is-playing` marks an explicit user choice; under prefers-reduced-motion the
        // CSS keeps the ticker still until this class is present.
        marquee.classList.toggle('is-playing', ! paused);
        control.setAttribute('aria-pressed', paused ? 'true' : 'false');
        control.textContent = paused ? playLabel : pauseLabel;
    };

    control.addEventListener('click', () => {
        setPaused(! marquee.classList.contains('is-paused'));
    });

    if (reducedMotion.matches) {
        setPaused(true);
    }

    reducedMotion.addEventListener('change', (event) => {
        if (event.matches) {
            setPaused(true);
        }
    });

    // Constant scrolling speed: duration = distance of one group / px per second.
    const group = marquee.querySelector('.landing-marquee__group');

    if (!(group instanceof HTMLElement)) {
        return;
    }

    const syncDuration = () => {
        const width = group.getBoundingClientRect().width;

        if (width <= 0) {
            return;
        }

        const seconds = Math.max(MARQUEE_MIN_DURATION_S, width / MARQUEE_SPEED_PX_PER_S);
        marquee.style.setProperty('--landing-marquee-duration', `${seconds.toFixed(2)}s`);
    };

    syncDuration();

    if ('ResizeObserver' in window) {
        // Also fires when Nunito Sans swaps in and the track re-measures.
        new ResizeObserver(syncDuration).observe(group);
    } else {
        window.addEventListener('resize', syncDuration, { passive: true });
    }
}

function setupReveal() {
    const targets = Array.from(document.querySelectorAll('.landing-reveal'));

    if (targets.length === 0) {
        return;
    }

    if (reducedMotion.matches || ! ('IntersectionObserver' in window)) {
        revealAll();

        return;
    }

    reducedMotion.addEventListener('change', (event) => {
        if (event.matches) {
            revealAll();
        }
    });

    targets.forEach((el) => {
        // The observed element stays in flow; the part that actually moves is the
        // inner target when present (cards) or the element itself (closing CTA).
        const moving = el.querySelector(':scope > .landing-reveal__target') ?? el;

        // Once the entrance transition finishes, drop the stagger so later
        // transitions (hover, theme switch) are immediate.
        moving.addEventListener(
            'transitionend',
            (event) => {
                if (event.target === moving && event.propertyName === 'transform') {
                    el.style.removeProperty('--reveal-delay');
                    el.classList.add('is-settled');
                }
            },
            { passive: true },
        );
    });

    const observer = new IntersectionObserver(
        (entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);

            visible.forEach((entry, index) => {
                const step = Math.min(index, REVEAL_MAX_STAGGER_STEPS);
                entry.target.style.setProperty('--reveal-delay', `${step * REVEAL_STAGGER_MS}ms`);
                entry.target.classList.add('is-inview');
                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -6% 0px' },
    );

    targets.forEach((el) => observer.observe(el));
}

export function initLandingMotion() {
    document.documentElement.classList.add('has-js');
    document.querySelectorAll('[data-marquee-toggle]').forEach(setupMarquee);
    setupReveal();
}
