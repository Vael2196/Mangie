document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.querySelector('[data-avatar-file]');
    const modal = document.querySelector('[data-avatar-crop-modal]');

    if (!fileInput || !modal) {
        return;
    }

    const canvas = modal.querySelector('[data-avatar-canvas]');
    const zoomInput = modal.querySelector('[data-avatar-zoom]');
    const confirmButton = modal.querySelector('[data-confirm-avatar-crop]');
    const cancelButtons = modal.querySelectorAll('[data-cancel-avatar-crop]');
    const avatarData = document.querySelector('[data-avatar-data]');
    const removeAvatar = document.querySelector('[data-remove-avatar-value]');
    const removeButton = document.querySelector('[data-remove-avatar]');
    const errorElement = document.querySelector('[data-avatar-error]');
    const preview = document.querySelector('[data-profile-avatar-preview]');
    const context = canvas.getContext('2d');

    const size = canvas.width;
    let image = null;
    let minimumScale = 1;
    let scale = 1;
    let offsetX = 0;
    let offsetY = 0;
    let activePointer = null;
    let previousPoint = null;

    function showError(message) {
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    }

    function clearError() {
        errorElement.textContent = '';
        errorElement.classList.add('hidden');
    }

    function openModal() {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal({ resetInput = false } = {}) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');

        if (resetInput) {
            fileInput.value = '';
        }
    }

    function clampOffsets() {
        const width = image.width * scale;
        const height = image.height * scale;

        offsetX = Math.min(0, Math.max(size - width, offsetX));
        offsetY = Math.min(0, Math.max(size - height, offsetY));
    }

    function draw() {
        if (!image) {
            return;
        }

        clampOffsets();
        context.clearRect(0, 0, size, size);
        context.drawImage(
            image,
            offsetX,
            offsetY,
            image.width * scale,
            image.height * scale
        );
    }

    function pointFor(event) {
        const bounds = canvas.getBoundingClientRect();

        return {
            x: (event.clientX - bounds.left) * (canvas.width / bounds.width),
            y: (event.clientY - bounds.top) * (canvas.height / bounds.height),
        };
    }

    function loadImage(file) {
        clearError();

        if (!file.type.startsWith('image/')) {
            showError('Please choose an image file.');
            fileInput.value = '';
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            showError('Profile photos must be 5 MB or smaller.');
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();

        reader.addEventListener('load', () => {
            const nextImage = new Image();

            nextImage.addEventListener('load', () => {
                image = nextImage;
                minimumScale = Math.max(
                    size / image.width,
                    size / image.height
                );
                scale = minimumScale;
                offsetX = (size - image.width * scale) / 2;
                offsetY = (size - image.height * scale) / 2;
                zoomInput.value = '1';
                draw();
                openModal();
            });

            nextImage.addEventListener('error', () => {
                showError('That image could not be opened.');
                fileInput.value = '';
            });

            nextImage.src = reader.result;
        });

        reader.readAsDataURL(file);
    }

    fileInput.addEventListener('change', () => {
        const [file] = fileInput.files;

        if (file) {
            loadImage(file);
        }
    });

    zoomInput.addEventListener('input', () => {
        if (!image) {
            return;
        }

        const imageCenterX = (size / 2 - offsetX) / scale;
        const imageCenterY = (size / 2 - offsetY) / scale;

        scale = minimumScale * Number(zoomInput.value);
        offsetX = size / 2 - imageCenterX * scale;
        offsetY = size / 2 - imageCenterY * scale;
        draw();
    });

    canvas.addEventListener('pointerdown', event => {
        if (!image) {
            return;
        }

        activePointer = event.pointerId;
        previousPoint = pointFor(event);
        canvas.setPointerCapture(event.pointerId);
        canvas.classList.add('cursor-grabbing');
    });

    canvas.addEventListener('pointermove', event => {
        if (event.pointerId !== activePointer || !previousPoint) {
            return;
        }

        const point = pointFor(event);
        offsetX += point.x - previousPoint.x;
        offsetY += point.y - previousPoint.y;
        previousPoint = point;
        draw();
    });

    function stopDragging(event) {
        if (event.pointerId !== activePointer) {
            return;
        }

        activePointer = null;
        previousPoint = null;
        canvas.classList.remove('cursor-grabbing');
    }

    canvas.addEventListener('pointerup', stopDragging);
    canvas.addEventListener('pointercancel', stopDragging);

    confirmButton.addEventListener('click', () => {
        if (!image) {
            return;
        }

        const cropped = canvas.toDataURL('image/jpeg', 0.9);
        avatarData.value = cropped;
        removeAvatar.value = '0';

        if (preview && window.MangieAvatars) {
            window.MangieAvatars.update({
                id: Number(preview.dataset.userAvatarId),
                name: preview.dataset.userAvatarName,
                avatar_url: cropped,
            });
        }

        closeModal();
    });

    cancelButtons.forEach(button => {
        button.addEventListener('click', () => {
            closeModal({ resetInput: true });
        });
    });

    removeButton?.addEventListener('click', () => {
        avatarData.value = '';
        removeAvatar.value = '1';
        fileInput.value = '';

        if (preview && window.MangieAvatars) {
            window.MangieAvatars.update({
                id: Number(preview.dataset.userAvatarId),
                name: preview.dataset.userAvatarName,
                avatar_url: null,
            });
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal({ resetInput: true });
        }
    });
});
