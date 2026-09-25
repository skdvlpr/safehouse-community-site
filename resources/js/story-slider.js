export function initStorySlider() {
    document.querySelectorAll('[data-story-slider]').forEach((root) => {
        const track = root.querySelector('[data-story-track]');
        const viewport = root.querySelector('.story-slider__viewport');
        const nextButton = root.querySelector('[data-story-next]');
        const prevButton = root.querySelector('[data-story-prev]');

        if (!track || !viewport) {
            return;
        }

        let index = 0;
        let timer = null;
        let startX = 0;
        let didSwipe = false;

        function cardWidth() {
            const card = track.children[0];

            if (!card) {
                return 0;
            }

            const styles = window.getComputedStyle(track);
            const gap = Number.parseFloat(styles.columnGap || styles.gap || '16') || 16;

            return card.getBoundingClientRect().width + gap;
        }

        function goTo(nextIndex) {
            const count = track.children.length;

            if (count < 2) {
                return;
            }

            index = (nextIndex + count) % count;
            track.style.transform = 'translateX(' + -index * cardWidth() + 'px)';
        }

        function next() {
            goTo(index + 1);
        }

        function prev() {
            goTo(index - 1);
        }

        function stop() {
            if (timer !== null) {
                window.clearInterval(timer);
                timer = null;
            }
        }

        function start() {
            stop();

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            if (track.children.length < 2) {
                return;
            }

            timer = window.setInterval(next, 5000);
        }

        nextButton?.addEventListener('click', () => {
            stop();
            next();
        });

        prevButton?.addEventListener('click', () => {
            stop();
            prev();
        });

        viewport.addEventListener('pointerdown', (event) => {
            startX = event.clientX;
            didSwipe = false;
        });

        viewport.addEventListener('pointerup', (event) => {
            const dx = event.clientX - startX;

            if (Math.abs(dx) < 40) {
                return;
            }

            didSwipe = true;
            stop();

            if (dx < 0) {
                next();
            } else {
                prev();
            }
        });

        viewport.addEventListener(
            'click',
            (event) => {
                if (!didSwipe) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                didSwipe = false;
            },
            true,
        );

        window.addEventListener('resize', () => {
            goTo(index);
        }, { passive: true });

        start();
    });
}
