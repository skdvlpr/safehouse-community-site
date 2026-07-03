export function initSportelloSelects() {
    document.querySelectorAll('[data-sportello-select]').forEach((root) => {
        if (root.dataset.sportelloSelectReady === 'true') {
            return;
        }

        root.dataset.sportelloSelectReady = 'true';

        const trigger = root.querySelector('[data-sportello-select-trigger]');
        const menu = root.querySelector('[data-sportello-select-menu]');
        const hiddenInput = root.querySelector('[data-sportello-select-input]');
        const valueEl = root.querySelector('[data-sportello-select-value]');
        const options = Array.from(root.querySelectorAll('[data-sportello-select-option]'));

        if (!trigger || !menu || !hiddenInput || !valueEl || options.length === 0) {
            return;
        }

        let activeIndex = options.findIndex(
            (option) => option.dataset.value === hiddenInput.value,
        );

        if (activeIndex < 0) {
            activeIndex = 0;
            selectOption(options[0], false);
        }

        trigger.addEventListener('click', () => {
            if (menu.hidden) {
                openMenu();
            } else {
                closeMenu();
            }
        });

        options.forEach((option, index) => {
            option.addEventListener('click', () => {
                selectOption(option, true);
                closeMenu();
            });

            option.addEventListener('mouseenter', () => {
                setActiveIndex(index, false);
            });
        });

        document.addEventListener('click', (event) => {
            if (!root.contains(event.target)) {
                closeMenu();
            }
        });

        trigger.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (menu.hidden) {
                    openMenu();
                } else {
                    setActiveIndex(Math.min(activeIndex + 1, options.length - 1), true);
                }
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (menu.hidden) {
                    openMenu();
                } else {
                    setActiveIndex(Math.max(activeIndex - 1, 0), true);
                }
            } else if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                if (menu.hidden) {
                    openMenu();
                } else if (activeIndex >= 0) {
                    selectOption(options[activeIndex], true);
                    closeMenu();
                }
            } else if (event.key === 'Escape') {
                closeMenu();
            }
        });

        function openMenu() {
            menu.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            root.classList.add('is-open');
            setActiveIndex(activeIndex >= 0 ? activeIndex : 0, false);
        }

        function closeMenu() {
            menu.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            root.classList.remove('is-open');
        }

        function setActiveIndex(index, focusOption) {
            activeIndex = index;

            options.forEach((option, optionIndex) => {
                const isActive = optionIndex === index;
                option.classList.toggle('is-active', isActive);

                if (focusOption && isActive) {
                    option.focus();
                }
            });
        }

        function selectOption(option, dispatchChange) {
            const value = option.dataset.value ?? '';
            const label = option.textContent?.trim() ?? '';

            hiddenInput.value = value;
            valueEl.textContent = label;

            options.forEach((item) => {
                const selected = item === option;
                item.setAttribute('aria-selected', selected ? 'true' : 'false');
                item.classList.toggle('is-selected', selected);
            });

            activeIndex = options.indexOf(option);

            if (dispatchChange) {
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    });
}
