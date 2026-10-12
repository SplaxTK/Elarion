document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.querySelector('[data-auth-overlay]');
    const dialog = document.querySelector('#auth-dialog');
    const openButton = document.querySelector('[data-auth-open]');

    if (!overlay || !dialog || !openButton) {
        return;
    }

    const closeButton = dialog.querySelector('[data-auth-close]');
    const panels = dialog.querySelectorAll('[data-auth-panel]');
    let previousFocus = null;

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

    const openDialog = () => {
        previousFocus = document.activeElement;
        showPanel('login');
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

    openButton.addEventListener('click', openDialog);
    closeButton?.addEventListener('click', closeDialog);

    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            closeDialog();
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