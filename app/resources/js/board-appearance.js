import { jsonRequest } from './http';
import { applyMutationResponse } from './realtime-reconciler';

document.addEventListener('DOMContentLoaded', () => {
    const panel = document.querySelector('[data-board-appearance-panel]');

    if (!panel) {
        return;
    }

    const endpoint = panel.dataset.endpoint;
    const toggle = document.querySelector('[data-board-appearance-toggle]');
    const close = panel.querySelector('[data-board-appearance-close]');
    const imageInput = panel.querySelector('[data-board-background-image]');
    const reset = panel.querySelector('[data-board-background-reset]');
    const status = panel.querySelector('[data-board-appearance-status]');
    const controls = panel.querySelectorAll('button, input');

    function setOpen(open) {
        panel.classList.toggle('hidden', !open);
        toggle?.setAttribute('aria-expanded', String(open));
    }

    function setBusy(busy, message = '') {
        controls.forEach(control => {
            control.disabled = busy;
        });

        status.textContent = message;
        status.classList.toggle('hidden', !message);
        status.classList.remove('text-red-600', 'dark:text-red-400');
    }

    function showError(error) {
        status.textContent = error.message
            ?? 'The board background could not be changed.';
        status.classList.remove('hidden');
        status.classList.add('text-red-600', 'dark:text-red-400');
    }

    async function save(formData, busyMessage) {
        setBusy(true, busyMessage);
        formData.set('_method', 'PATCH');

        try {
            const data = await jsonRequest(endpoint, {
                method: 'POST',
                body: formData,
            });

            await applyMutationResponse(data);
            setBusy(false, 'Background saved.');

            window.setTimeout(() => {
                status.classList.add('hidden');
            }, 1800);
        } catch (error) {
            setBusy(false);
            showError(error);
        } finally {
            if (imageInput) {
                imageInput.value = '';
            }
        }
    }

    toggle?.addEventListener('click', event => {
        event.stopPropagation();
        setOpen(panel.classList.contains('hidden'));
    });

    close?.addEventListener('click', () => setOpen(false));

    panel.addEventListener('click', event => event.stopPropagation());
    document.addEventListener('click', () => setOpen(false));

    panel
        .querySelectorAll('[data-board-background-color]')
        .forEach(button => {
            button.addEventListener('click', () => {
                const data = new FormData();
                data.set('mode', 'color');
                data.set('background_color', button.dataset.boardBackgroundColor);
                void save(data, 'Saving colour…');
            });
        });

    imageInput?.addEventListener('change', () => {
        const [file] = imageInput.files;

        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            showError(new Error('Please choose an image file.'));
            imageInput.value = '';
            return;
        }

        if (file.size > 8 * 1024 * 1024) {
            showError(new Error('Board backgrounds must be 8 MB or smaller.'));
            imageInput.value = '';
            return;
        }

        const data = new FormData();
        data.set('mode', 'image');
        data.set('background_image', file);
        void save(data, 'Uploading image…');
    });

    reset?.addEventListener('click', () => {
        const data = new FormData();
        data.set('mode', 'default');
        void save(data, 'Restoring default…');
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
});
