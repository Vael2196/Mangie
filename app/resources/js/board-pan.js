const INTERACTIVE_SELECTOR = [
    'button',
    'a',
    'input',
    'textarea',
    'select',
    'label',
    '[contenteditable="true"]',
    '[data-task-id]',
    '[data-task-list]',
].join(',');

document.addEventListener('DOMContentLoaded', () => {
    const board = document.querySelector('[data-board-pan]');

    if (!board) {
        return;
    }

    let pointerId = null;
    let startX = 0;
    let startY = 0;
    let startScrollLeft = 0;
    let startScrollTop = 0;
    let moved = false;

    board.addEventListener('pointerdown', event => {
        const isMiddleMouse = event.button === 1;
        const isEmptyLeftClick = event.button === 0
            && !event.target.closest(INTERACTIVE_SELECTOR);

        if (!isMiddleMouse && !isEmptyLeftClick) {
            return;
        }

        pointerId = event.pointerId;
        startX = event.clientX;
        startY = event.clientY;
        startScrollLeft = board.scrollLeft;
        startScrollTop = board.scrollTop;
        moved = false;
        board.setPointerCapture(pointerId);
        board.classList.add('is-panning');
        document.body.classList.add('board-panning');
        event.preventDefault();
    });

    board.addEventListener('pointermove', event => {
        if (event.pointerId !== pointerId) {
            return;
        }

        const deltaX = event.clientX - startX;
        const deltaY = event.clientY - startY;

        if (Math.abs(deltaX) > 3 || Math.abs(deltaY) > 3) {
            moved = true;
        }

        board.scrollLeft = startScrollLeft - deltaX;
        board.scrollTop = startScrollTop - deltaY;
        event.preventDefault();
    });

    const stopPanning = event => {
        if (event.pointerId !== pointerId) {
            return;
        }

        if (board.hasPointerCapture(pointerId)) {
            board.releasePointerCapture(pointerId);
        }

        pointerId = null;
        board.classList.remove('is-panning');
        document.body.classList.remove('board-panning');

        if (moved) {
            window.setTimeout(() => {
                moved = false;
            }, 0);
        }
    };

    board.addEventListener('pointerup', stopPanning);
    board.addEventListener('pointercancel', stopPanning);
    board.addEventListener('auxclick', event => {
        if (event.button === 1) {
            event.preventDefault();
        }
    });

    board.addEventListener('click', event => {
        if (!moved) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        moved = false;
    }, true);
});
