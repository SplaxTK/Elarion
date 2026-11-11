document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.querySelector('[data-auth-overlay]');
    const dialog = document.querySelector('#auth-dialog');
    const openButton = document.querySelector('[data-auth-open]');
    const userMenuButton = document.querySelector('[data-user-menu-button]');
    const userMenu = document.querySelector('[data-user-menu]');
    const logoutDialog = document.querySelector('[data-logout-dialog]');
    const logoutOpenButton = document.querySelector('[data-logout-open]');
    const logoutCloseButton = document.querySelector('[data-logout-close]');
    let previousFocus = null;

    const closeUserMenu = () => {
        if (!userMenu || !userMenuButton) {
            return;
        }
        userMenu.hidden = true;
        userMenuButton.setAttribute('aria-expanded', 'false');
    };

    const openUserMenu = () => {
        if (!userMenu || !userMenuButton) {
            return;
        }
        const isOpen = userMenu.hidden === false;
        userMenu.hidden = isOpen;
        userMenuButton.setAttribute('aria-expanded', String(!isOpen));
    };

    userMenuButton?.addEventListener('click', (event) => {
        event.stopPropagation();
        if (!userMenu) {
            return;
        }
        const shouldOpen = userMenu.hidden;
        userMenu.hidden = !shouldOpen;
        userMenuButton.setAttribute('aria-expanded', String(shouldOpen));
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (userMenu && userMenuButton && !userMenu.contains(target) && !userMenuButton.contains(target)) {
            closeUserMenu();
        }

        if (logoutDialog && !logoutDialog.contains(target) && !logoutOpenButton?.contains(target)) {
            logoutDialog.hidden = true;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeUserMenu();
            if (logoutDialog) {
                logoutDialog.hidden = true;
            }
        }
    });

    logoutOpenButton?.addEventListener('click', () => {
        if (logoutDialog) {
            logoutDialog.hidden = false;
        }
        closeUserMenu();
    });

    logoutCloseButton?.addEventListener('click', () => {
        if (logoutDialog) {
            logoutDialog.hidden = true;
        }
    });

    if (!overlay || !dialog || !openButton) {
        return;
    }

    const closeButton = dialog.querySelector('[data-auth-close]');
    const panels = dialog.querySelectorAll('[data-auth-panel]');

    const showPanel = (name) => {
        panels.forEach((panel) => {
            panel.hidden = panel.dataset.authPanel !== name;
            const heading = panel.querySelector('h2');
            if (heading) {
                heading.removeAttribute('id');
            }
        });
        const heading = dialog.querySelector(`[data-auth-panel="${name}"] h2`);
        if (heading) {
            heading.id = 'auth-title';
        }
    };

    const openDialog = (panel = 'login') => {
        previousFocus = document.activeElement;
        showPanel(panel);
        overlay.hidden = false;
        document.body.classList.add('auth-open');
        dialog.focus();
    };

    const closeDialog = () => {
        overlay.hidden = true;
        document.body.classList.remove('auth-open');
        if (previousFocus instanceof HTMLElement) {
            previousFocus.focus();
        }
    };

    if (window.ELARION_AUTH_OPEN) {
        openDialog(window.ELARION_AUTH_OPEN);
    }

    openButton.addEventListener('click', () => {
        openDialog();
    });
    closeButton?.addEventListener('click', closeDialog);

    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            const bounds = dialog.getBoundingClientRect();
            const distanceX = Math.max(bounds.left - event.clientX, 0, event.clientX - bounds.right);
            const distanceY = Math.max(bounds.top - event.clientY, 0, event.clientY - bounds.bottom);

            if (Math.hypot(distanceX, distanceY) >= 80) {
                closeDialog();
            }
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !overlay.hidden) {
            closeDialog();
        }
    });

    dialog.querySelectorAll('[data-auth-show]').forEach((button) => {
        button.addEventListener('click', () => {
            showPanel(button.dataset.authShow);
            dialog.focus();
        });
    });

    dialog.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = dialog.querySelector(`#${button.dataset.passwordToggle}`);
            if (input instanceof HTMLInputElement) {
                input.type = input.type === 'password' ? 'text' : 'password';
                button.setAttribute('aria-label', input.type === 'password' ? 'Mostrar senha' : 'Ocultar senha');
            }
        });
    });

    const registerForm = dialog.querySelector('[data-auth-panel="register"]');
    const password = dialog.querySelector('#register-password');
    const passwordConfirmation = dialog.querySelector('#register-password-confirm');
    if (registerForm instanceof HTMLFormElement
        && password instanceof HTMLInputElement
        && passwordConfirmation instanceof HTMLInputElement) {
        const validatePasswordConfirmation = () => {
            passwordConfirmation.setCustomValidity(
                passwordConfirmation.value && passwordConfirmation.value !== password.value
                    ? 'As senhas não coincidem.'
                    : '',
            );
        };
        password.addEventListener('input', validatePasswordConfirmation);
        passwordConfirmation.addEventListener('input', validatePasswordConfirmation);
    }
});