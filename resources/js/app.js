import './bootstrap';

import * as bootstrap from 'bootstrap';
import Alpine from 'alpinejs';

window.bootstrap = bootstrap;
window.Alpine = Alpine;

Alpine.start();

/**
 * Guard against duplicate submissions: disable the submit button and show a
 * spinner once a form is on its way. Opt in with data-submit-guard on the form.
 */
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-submit-guard')) {
        return;
    }

    // Let native validation block the submit before we lock the button.
    if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
        return;
    }

    if (form.dataset.submitted === 'true') {
        event.preventDefault();
        return;
    }

    form.dataset.submitted = 'true';

    form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.classList.add('is-loading');
        // Disabling before submit would drop the button's own value, so defer.
        window.setTimeout(() => {
            button.disabled = true;
        }, 0);
    });
}, true);

/** Admin sidebar toggle (mobile). */
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const backdrop = document.querySelector('[data-admin-backdrop]');

    const close = () => {
        sidebar?.classList.remove('is-open');
        backdrop?.classList.remove('is-open');
    };

    document.querySelectorAll('[data-sidebar-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            sidebar?.classList.toggle('is-open');
            backdrop?.classList.toggle('is-open');
        });
    });

    backdrop?.addEventListener('click', close);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });

    // Auto-dismiss transient flash alerts.
    document.querySelectorAll('[data-auto-dismiss]').forEach((alert) => {
        window.setTimeout(() => {
            bootstrap.Alert.getOrCreateInstance(alert)?.close();
        }, 6000);
    });
});
