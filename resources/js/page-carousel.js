export function initPageCarousels(root = document) {
    root.querySelectorAll('[data-page-carousel]').forEach((carousel) => {
        if (carousel.dataset.carouselReady === 'true') {
            return;
        }

        let slides = [];

        try {
            slides = JSON.parse(carousel.dataset.slides || '[]');
        } catch {
            return;
        }

        if (!Array.isArray(slides) || slides.length === 0) {
            return;
        }

        const main = carousel.querySelector('[data-carousel-main]');
        const thumbs = [...carousel.querySelectorAll('[data-carousel-thumb]')];
        const prev = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');

        if (!main) {
            return;
        }

        let active = 0;

        const show = (index) => {
            active = (index + slides.length) % slides.length;
            const slide = slides[active];

            main.src = slide.url;
            main.alt = slide.alt || '';

            thumbs.forEach((thumb, thumbIndex) => {
                thumb.classList.toggle('is-active', thumbIndex === active);
                thumb.setAttribute('aria-current', thumbIndex === active ? 'true' : 'false');
            });
        };

        thumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                const index = Number.parseInt(thumb.dataset.index || '0', 10);
                show(index);
            });
        });

        prev?.addEventListener('click', () => show(active - 1));
        next?.addEventListener('click', () => show(active + 1));

        carousel.addEventListener('keydown', (event) => {
            if (slides.length < 2) {
                return;
            }

            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                show(active - 1);
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                show(active + 1);
            }
        });

        carousel.tabIndex = slides.length > 1 ? 0 : -1;
        carousel.dataset.carouselReady = 'true';
    });
}

document.addEventListener('DOMContentLoaded', () => initPageCarousels());
