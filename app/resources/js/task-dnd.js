import Sortable from 'sortablejs';


const LIST_SELECTOR =
    '[data-task-list]';

const TASK_SELECTOR =
    '[data-task-id]';


function csrfToken() {
    return document
        .querySelector(
            'meta[name="csrf-token"]'
        )
        ?.getAttribute('content');
}


function taskElements(list) {
    return Array.from(
        list.querySelectorAll(
            `:scope > ${TASK_SELECTOR}`
        )
    );
}


function refreshEmptyMarker(list) {

    const marker =
        list.querySelector(
            ':scope > [data-empty-drop-marker]'
        );

    if (!marker) {
        return;
    }


    marker.classList.toggle(
        'hidden',
        taskElements(list).length > 0
    );
}


function refreshAllEmptyMarkers() {

    document
        .querySelectorAll(
            LIST_SELECTOR
        )
        .forEach(
            refreshEmptyMarker
        );
}


function findColumnList(columnId) {

    return document.querySelector(
        `${LIST_SELECTOR}` +
        `[data-column-id="${columnId}"]`
    );
}


function findBoardList(boardId) {

    return document.querySelector(
        `${LIST_SELECTOR}` +
        `[data-board-id="${boardId}"]`
    );
}


function setColumnCount(
    columnId,
    count
) {
    const column =
        document.getElementById(
            `column-${columnId}`
        );

    if (!column) {
        return;
    }


    const value =
        column.querySelector(
            '[data-task-count-value]'
        );

    if (value) {
        value.textContent = count;
    }

    const deleteButton =
        column.querySelector(
            '[data-delete-column]'
        );

    if (deleteButton) {
        deleteButton.disabled =
            count !== 0;
    }


    const deleteHelp =
        column.querySelector(
            '[data-delete-column-help]'
        );

    if (deleteHelp) {
        deleteHelp.classList.toggle(
            'hidden',
            count === 0
        );
    }
}


function setIssueCount(count) {

    const counter =
        document.getElementById(
            'issues-count'
        );

    if (counter) {
        counter.textContent = count;
    }
}


function updateCounts(data) {

    setColumnCount(
        data.source_column_id,
        data.source_column_count
    );


    setColumnCount(
        data.target_column_id,
        data.target_column_count
    );


    setIssueCount(
        data.backlog_issue_count
    );
}


function insertAtPosition(
    list,
    item,
    position
) {
    const items =
        taskElements(list)
            .filter(
                element =>
                    element !== item
            );


    const index =
        Math.max(
            0,
            Math.min(
                position - 1,
                items.length
            )
        );


    if (index >= items.length) {

        const marker =
            list.querySelector(
                ':scope > [data-empty-drop-marker]'
            );

        if (marker) {
            list.insertBefore(
                item,
                marker
            );
        } else {
            list.appendChild(item);
        }

    } else {

        list.insertBefore(
            item,
            items[index]
        );

    }


    refreshEmptyMarker(list);
}


async function sendMove(
    taskId,
    targetColumnId,
    targetPosition
) {
    const headers = {
        'X-CSRF-TOKEN':
            csrfToken(),

        'Content-Type':
            'application/json',

        'Accept':
            'application/json',
    };

    const socketId =
        window.Echo?.socketId?.();

    if (socketId) {
        headers['X-Socket-ID'] =
            socketId;
    }


    const response =
        await fetch(
            `/tasks/${taskId}/move`,
            {
                method: 'PATCH',

                headers,

                body: JSON.stringify({
                    target_column_id:
                        targetColumnId,

                    target_position:
                        targetPosition,
                }),
            }
        );


    const data =
        await response.json();


    if (
        !response.ok ||
        !data.success
    ) {
        throw new Error(
            data.message
            ?? 'Could not move task.'
        );
    }


    return data;
}


function restoreOriginalPosition(
    event
) {
    const source =
        event.from;

    const item =
        event.item;

    const oldIndex =
        event.oldDraggableIndex
        ?? event.oldIndex
        ?? 0;


    insertAtPosition(
        source,
        item,
        oldIndex + 1
    );


    refreshEmptyMarker(
        event.to
    );
}


function initSortable(list) {

    if (list.dataset.sortableReady) {
        return;
    }

    list.dataset.sortableReady = '1';


    new Sortable(list, {

        group: {
            name: 'mangie-tasks',
            pull: true,
            put: true,
        },

        draggable:
            TASK_SELECTOR,

        animation: 160,

        easing:
            'cubic-bezier(0.2, 0, 0, 1)',

        ghostClass:
            'task-sortable-ghost',

        chosenClass:
            'task-sortable-chosen',

        dragClass:
            'task-sortable-dragging',

        emptyInsertThreshold: 40,

        fallbackTolerance: 4,

        sort:
            list.dataset.reorder
            !== 'false',


        onStart(event) {

            event.item.dataset.wasDragged =
                '1';

            document.body.classList.add(
                'task-dragging'
            );
        },


        async onEnd(event) {

            document.body.classList.remove(
                'task-dragging'
            );


            const taskId =
                Number(
                    event.item
                        .dataset
                        .taskId
                );


            const targetColumnId =
                Number(
                    event.to
                        .dataset
                        .columnId
                );


            if (
                !taskId ||
                !targetColumnId
            ) {
                restoreOriginalPosition(
                    event
                );

                return;
            }


            const sameContainer =
                event.from === event.to;

            const sameIndex =
                (
                    event.oldDraggableIndex
                    ?? event.oldIndex
                )
                ===
                (
                    event.newDraggableIndex
                    ?? event.newIndex
                );


            if (
                sameContainer &&
                sameIndex
            ) {
                delete event.item
                    .dataset
                    .wasDragged;

                return;
            }

            refreshEmptyMarker(
                event.from
            );

            refreshEmptyMarker(
                event.to
            );


            const shouldAppend =
                event.to.dataset.reorder
                === 'false';


            const targetPosition =
                shouldAppend
                    ? Number.MAX_SAFE_INTEGER
                    : (
                        (
                            event.newDraggableIndex
                            ?? event.newIndex
                            ?? 0
                        )
                        + 1
                    );


            try {

                const data =
                    await sendMove(
                        taskId,
                        targetColumnId,
                        targetPosition
                    );


                event.item.dataset.columnId =
                    data.target_column_id;


                updateCounts(data);

            } catch (error) {

                console.error(
                    'Task move failed:',
                    error
                );

                restoreOriginalPosition(
                    event
                );

            } finally {

                setTimeout(() => {
                    delete event.item
                        .dataset
                        .wasDragged;
                }, 100);

            }
        },
    });


    refreshEmptyMarker(list);
}


function realtimeDestination(data) {

    const context =
        document
            .querySelector(
                '[data-task-sync-context]'
            )
            ?.dataset
            ?.taskSyncContext
        ?? 'board';

    if (context === 'board') {

        return findColumnList(
            data.target_column_id
        );
    }

    return findBoardList(
        data.target_board_id
    );
}


function applyRemoteMove(data) {

    const task =
        document.querySelector(
            `${TASK_SELECTOR}` +
            `[data-task-id="${data.task_id}"]`
        );


    const target =
        realtimeDestination(data);

    if (task && target) {

        insertAtPosition(
            target,
            task,
            data.target_position
        );


        task.dataset.columnId =
            data.target_column_id;
    }

    if (task && !target) {

        const oldList =
            task.closest(
                LIST_SELECTOR
            );

        task.remove();

        if (oldList) {
            refreshEmptyMarker(
                oldList
            );
        }
    }


    updateCounts(data);

    refreshAllEmptyMarkers();
}


function subscribeRealtime() {

    if (!window.Echo) {
        return;
    }


    const root =
        document.querySelector(
            '[data-realtime-board-ids]'
        );


    if (!root) {
        return;
    }


    let boardIds = [];

    try {

        boardIds =
            JSON.parse(
                root.dataset
                    .realtimeBoardIds
                ?? '[]'
            );

    } catch (error) {

        console.error(
            'Invalid realtime board IDs.',
            error
        );

        return;
    }


    [
        ...new Set(boardIds)
    ].forEach(boardId => {

        window.Echo
            .private(
                `boards.${boardId}`
            )
            .listen(
                '.task.moved',
                applyRemoteMove
            );

    });
}


document.addEventListener(
    'DOMContentLoaded',
    () => {

        document
            .querySelectorAll(
                LIST_SELECTOR
            )
            .forEach(
                initSortable
            );


        refreshAllEmptyMarkers();

        subscribeRealtime();
    }
);