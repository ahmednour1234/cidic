/**
 * CV thumbnails.
 *
 * Renders the first page of each candidate's PDF into a canvas so the cards
 * show the real document rather than a stand-in photo. Rendering happens in
 * the browser because the server has no PDF rasteriser available.
 *
 * Thumbnails are rendered lazily (as cards scroll into view) and only a few at
 * a time, so a long listing does not fetch every PDF at once.
 */
import * as pdfjs from 'pdfjs-dist/build/pdf.mjs';
import workerUrl from 'pdfjs-dist/build/pdf.worker.mjs?url';

pdfjs.GlobalWorkerOptions.workerSrc = workerUrl;

const RENDERED = 'is-rendered';
const FAILED = 'is-failed';

/** Draw page 1 of `url` into `canvas`, sized to the canvas's CSS box. */
async function renderThumb(canvas, url) {
    const task = pdfjs.getDocument({
        url,
        // Thumbnails never need fonts/annotations from external sources.
        disableAutoFetch: true,
        disableStream: false,
    });

    const pdf = await task.promise;
    const page = await pdf.getPage(1);

    // Match the canvas's on-screen size, allowing for high-DPI screens, so the
    // thumbnail is crisp without rendering a full-resolution page.
    const ratio = Math.min(window.devicePixelRatio || 1, 2);
    const box = canvas.getBoundingClientRect();
    const width = (box.width || 320) * ratio;

    const base = page.getViewport({ scale: 1 });
    const viewport = page.getViewport({ scale: width / base.width });

    canvas.width = Math.floor(viewport.width);
    canvas.height = Math.floor(viewport.height);

    await page.render({
        canvasContext: canvas.getContext('2d', { alpha: false }),
        viewport,
    }).promise;

    // Free the worker's copy once the bitmap is on the canvas.
    pdf.cleanup();
    pdf.destroy();
}

function init() {
    const targets = document.querySelectorAll('[data-cv-thumb]:not(.' + RENDERED + ')');

    if (!targets.length) {
        return;
    }

    // Without IntersectionObserver, render the first few and leave the rest
    // showing their placeholder rather than hammering the network.
    if (!('IntersectionObserver' in window)) {
        [...targets].slice(0, 4).forEach(run);
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                observer.unobserve(entry.target);
                run(entry.target);
            });
        },
        { rootMargin: '250px 0px' },
    );

    targets.forEach((el) => observer.observe(el));
}

/** Render one wrapper's canvas, flipping state classes for the CSS. */
function run(wrapper) {
    const canvas = wrapper.querySelector('canvas');
    const url = wrapper.dataset.cvThumb;

    if (!canvas || !url) {
        return;
    }

    renderThumb(canvas, url)
        .then(() => wrapper.classList.add(RENDERED))
        .catch(() => wrapper.classList.add(FAILED));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
} else {
    init();
}
