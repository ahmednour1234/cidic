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

/**
 * Scroll reveal.
 *
 * The `js` class on <html> is what arms the CSS start-state, so it is only
 * added when this script actually runs — without JS the content renders
 * plainly rather than staying stuck at opacity 0.
 *
 * Elements opt in with data-reveal (optionally "left" | "right" | "zoom"),
 * and data-reveal-delay="120" for a stagger in milliseconds.
 */
(() => {
    const root = document.documentElement;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

    // No IntersectionObserver, or the user asked for less motion: do nothing.
    // The absence of `.js` leaves every element at its natural visible state.
    if (!('IntersectionObserver' in window) || reduced.matches) {
        return;
    }

    root.classList.add('js');

    const reveal = (el) => {
        const delay = Number(el.dataset.revealDelay || 0);
        el.style.setProperty('--reveal-delay', `${delay}ms`);
        el.classList.add('is-revealed');
    };

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                reveal(entry.target);
                observer.unobserve(entry.target);
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 },
    );

    const watch = () => {
        document.querySelectorAll('[data-reveal]:not(.is-revealed)').forEach((el) => {
            // Anything already in view on load is revealed immediately, so the
            // first screen never waits for a scroll that may never come.
            if (el.getBoundingClientRect().top < window.innerHeight) {
                reveal(el);
            } else {
                observer.observe(el);
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', watch, { once: true });
    } else {
        watch();
    }

    // If the user switches on "reduce motion" mid-session, drop everything.
    reduced.addEventListener('change', (event) => {
        if (event.matches) {
            observer.disconnect();
            root.classList.remove('js');
        }
    });
})();
