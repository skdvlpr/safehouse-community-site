export function initHeaderDrawer() {
    const drawer = document.querySelector('[data-header-drawer]');
    const openButton = document.querySelector('[data-header-drawer-open]');

    if (!drawer || !openButton) {
        return;
    }

    const closeButtons = drawer.querySelectorAll('[data-header-drawer-close]');
    const panelLinks = drawer.querySelectorAll('a');
    const wideScreen = window.matchMedia('(min-width: 768px)');

    function isOpen() {
        return drawer.classList.contains('is-open');
    }

    function setOpen(open) {
        drawer.classList.toggle('is-open', open);
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        openButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('site-header-drawer-open', open);
    }

    openButton.addEventListener('click', () => {
        setOpen(!isOpen());
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => setOpen(false));
    });

    panelLinks.forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen()) {
            setOpen(false);
        }
    });

    wideScreen.addEventListener('change', (event) => {
        if (event.matches) {
            setOpen(false);
        }
    });
}
