export function initSocioDialog() {
    const dialog = document.getElementById('socio-dialog');

    if (!(dialog instanceof HTMLDialogElement)) {
        return;
    }

    const openDialog = () => {
        dialog.showModal();
        ensureTurnstile(dialog);
    };

    document.querySelectorAll('[data-socio-open]').forEach((control) => {
        control.addEventListener('click', (event) => {
            event.preventDefault();
            openDialog();
        });
    });

    document.querySelectorAll('[data-socio-close]').forEach((control) => {
        control.addEventListener('click', (event) => {
            event.preventDefault();
            dialog.close();
        });
    });

    if (dialog.hasAttribute('data-socio-had-errors')) {
        openDialog();
    }
}

function ensureTurnstile(dialog) {
    if (typeof window.turnstile === 'undefined') {
        return;
    }

    dialog.querySelectorAll('.cf-turnstile').forEach((el) => {
        if (!(el instanceof HTMLElement)) {
            return;
        }

        const existingId = el.getAttribute('data-widget-id');

        if (existingId) {
            window.turnstile.reset(existingId);

            return;
        }

        if (el.childElementCount > 0) {
            return;
        }

        const sitekey = el.getAttribute('data-sitekey');

        if (!sitekey) {
            return;
        }

        const theme = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
        const widgetId = window.turnstile.render(el, {
            sitekey,
            size: el.getAttribute('data-size') || 'flexible',
            theme,
        });

        el.setAttribute('data-widget-id', String(widgetId));
    });
}
