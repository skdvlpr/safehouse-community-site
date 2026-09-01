function createLightbox(carousel, slides, onNavigate, onClose) {
    if (!carousel.hasAttribute('data-carousel-lightbox')) {
        return null;
    }

    const closeLabel = carousel.dataset.lightboxClose || 'Close';
    const prevLabel = carousel.dataset.lightboxPrev || 'Previous';
    const nextLabel = carousel.dataset.lightboxNext || 'Next';

    const dialog = document.createElement('dialog');
    dialog.className = 'page-carousel-lightbox';
    dialog.innerHTML = `
        <div class="page-carousel-lightbox__inner">
            <button type="button" class="page-carousel-lightbox__close" data-lightbox-close aria-label="${closeLabel}">
                <span aria-hidden="true">×</span>
            </button>
            <div class="page-carousel-lightbox__controls">
                <button type="button" class="page-carousel-lightbox__nav page-carousel-lightbox__nav--prev" data-lightbox-prev aria-label="${prevLabel}" hidden>
                    <span aria-hidden="true">‹</span>
                </button>
                <div class="page-carousel-lightbox__frame">
                    <img src="" alt="" class="page-carousel-lightbox__image" data-lightbox-image decoding="async">
                </div>
                <button type="button" class="page-carousel-lightbox__nav page-carousel-lightbox__nav--next" data-lightbox-next aria-label="${nextLabel}" hidden>
                    <span aria-hidden="true">›</span>
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(dialog);

    const image = dialog.querySelector('[data-lightbox-image]');
    const closeButton = dialog.querySelector('[data-lightbox-close]');
    const prevButton = dialog.querySelector('[data-lightbox-prev]');
    const nextButton = dialog.querySelector('[data-lightbox-next]');

    const handleKeydown = (event) => {
        if (!dialog.open) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            close();

            return;
        }

        if (slides.length < 2) {
            return;
        }

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            onNavigate(-1);
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            onNavigate(1);
        }
    };

    const syncNav = (index) => {
        const hasMultiple = slides.length > 1;
        prevButton.hidden = !hasMultiple;
        nextButton.hidden = !hasMultiple;

        if (!image) {
            return;
        }

        const slide = slides[index];
        image.src = slide.url;
        image.alt = slide.alt || '';
    };

    const open = (index) => {
        syncNav(index);
        dialog.showModal();
        document.addEventListener('keydown', handleKeydown);
    };

    const close = () => {
        if (dialog.open) {
            dialog.close();
        }
    };

    closeButton?.addEventListener('click', close);

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            close();
        }
    });

    prevButton?.addEventListener('click', () => {
        onNavigate(-1);
    });

    nextButton?.addEventListener('click', () => {
        onNavigate(1);
    });

    dialog.addEventListener('close', () => {
        document.removeEventListener('keydown', handleKeydown);

        if (image) {
            image.removeAttribute('src');
        }

        onClose?.();
    });

    return {
        open,
        syncNav,
        close,
        isOpen: () => dialog.open,
    };
}

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
        const zoomTrigger = carousel.querySelector('[data-carousel-zoom]');
        const thumbs = [...carousel.querySelectorAll('[data-carousel-thumb]')];
        const prev = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');

        if (!main) {
            return;
        }

        let active = 0;
        let lightbox = null;

        const show = (index, options = {}) => {
            active = (index + slides.length) % slides.length;
            const slide = slides[active];

            main.src = slide.url;
            main.alt = slide.alt || '';

            thumbs.forEach((thumb, thumbIndex) => {
                thumb.classList.toggle('is-active', thumbIndex === active);
                thumb.setAttribute('aria-current', thumbIndex === active ? 'true' : 'false');
            });

            if (options.keepLightboxOpen) {
                lightbox?.syncNav(active);
            }
        };

        lightbox = createLightbox(
            carousel,
            slides,
            (delta) => {
                show(active + delta, { keepLightboxOpen: true });
            },
            () => {
                carousel.focus();
            },
        );

        const handleCarouselKeydown = (event) => {
            if (lightbox?.isOpen()) {
                return;
            }

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
        };

        thumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                const index = Number.parseInt(thumb.dataset.index || '0', 10);
                show(index);
                carousel.focus();
            });
        });

        prev?.addEventListener('click', () => {
            show(active - 1);
            carousel.focus();
        });

        next?.addEventListener('click', () => {
            show(active + 1);
            carousel.focus();
        });

        zoomTrigger?.addEventListener('click', () => {
            lightbox?.open(active);
        });

        carousel.addEventListener('keydown', handleCarouselKeydown);
        carousel.addEventListener('pointerdown', () => {
            carousel.focus();
        });

        carousel.tabIndex = 0;
        carousel.dataset.carouselReady = 'true';
    });
}

document.addEventListener('DOMContentLoaded', () => initPageCarousels());
