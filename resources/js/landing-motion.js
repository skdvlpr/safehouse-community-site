/**
 * Membership landing motion.
 *
 * - Values ticker: CSS animation, constant speed (px/s) regardless of how many
 *   values the CMS provides. On-page pause was removed (owner UAT). Reduced-motion
 *   still keeps the track still.
 *   https://www.w3.org/WAI/WCAG22/Understanding/pause-stop-hide.html
 * - Card entrance: IntersectionObserver adds `.is-inview`; cards that enter in the
 *   same frame get a short stagger.
 *   https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API
 * - prefers-reduced-motion: ticker stays paused, nothing slides, all content shown.
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

function setupMarquee(marquee) {
    if (!(marquee instanceof HTMLElement)) {
        return;
    }

    const viewport = marquee.querySelector('.landing-marquee__viewport');
    const track = marquee.querySelector('.landing-marquee__track');
    const source = marquee.querySelector('[data-marquee-source]');

    if (!(viewport instanceof HTMLElement) || !(track instanceof HTMLElement) || !(source instanceof HTMLElement)) {
        return;
    }

    const applyMotion = () => {
        const paused = reducedMotion.matches;
        marquee.classList.toggle('is-paused', paused);
        marquee.classList.toggle('is-playing', ! paused);
    };

    applyMotion();
    reducedMotion.addEventListener('change', applyMotion);

    const fill = () => {
        track.querySelectorAll('[data-marquee-clone]').forEach((node) => node.remove());

        const groupWidth = source.getBoundingClientRect().width;
        const viewWidth = viewport.getBoundingClientRect().width;

        if (groupWidth < 1 || viewWidth < 1) {
            return;
        }

        const perHalf = Math.max(1, Math.ceil(viewWidth / groupWidth));

        for (let index = 1; index < perHalf * 2; index += 1) {
            const clone = source.cloneNode(true);

            if (clone instanceof HTMLElement) {
                clone.setAttribute('aria-hidden', 'true');
                clone.dataset.marqueeClone = '1';
                track.appendChild(clone);
            }
        }

        const seconds = Math.max(MARQUEE_MIN_DURATION_S, (groupWidth * perHalf) / MARQUEE_SPEED_PX_PER_S);
        marquee.style.setProperty('--landing-marquee-duration', `${seconds.toFixed(2)}s`);
    };

    fill();

    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(fill);
        observer.observe(viewport);
        observer.observe(source);
    } else {
        window.addEventListener('resize', fill, { passive: true });
    }
}

function setupSwipeHint() {
    const hint = document.querySelector('.landing-swipe');

    if (!(hint instanceof HTMLElement)) {
        return;
    }

    let lastY = window.scrollY;

    const update = () => {
        const y = window.scrollY;
        const delta = y - lastY;

        if (y < 12) {
            hint.classList.remove('is-away');
        } else if (delta > 2) {
            hint.classList.add('is-away');
        } else if (delta < -2) {
            hint.classList.remove('is-away');
        }

        lastY = y;
    };

    let frame = 0;

    window.addEventListener('scroll', () => {
        if (frame !== 0) {
            return;
        }

        frame = window.requestAnimationFrame(() => {
            frame = 0;
            update();
        });
    }, { passive: true });
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
        const moving = el.querySelector(':scope > .landing-reveal__target') ?? el;

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
    document.querySelectorAll('.landing-marquee').forEach(setupMarquee);
    setupSwipeHint();
    setupReveal();
}
