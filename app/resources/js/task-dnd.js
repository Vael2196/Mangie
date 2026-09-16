document.addEventListener(
    'DOMContentLoaded',
    () => {

        const csrf =
            document.querySelector(
                'meta[name="csrf-token"]'
            );

        const moveBase =
            document.querySelector(
                'meta[name="task-move-base"]'
            );

        if (!csrf || !moveBase) {
            return;
        }


        const tasks =
            document.querySelectorAll(
                '[data-dnd-task="true"]'
            );

        const dropzones =
            document.querySelectorAll(
                '[data-task-dropzone="true"]'
            );

        if (
            !tasks.length ||
            !dropzones.length
        ) {
            return;
        }


        let draggedTask = null;
        let sourceZone = null;


        function clearDropzones() {

            dropzones.forEach(zone => {
                zone.classList.remove(
                    'ring-2',
                    'ring-indigo-400',
                    'ring-offset-2'
                );
            });

        }


        tasks.forEach(task => {

            task
                .querySelectorAll(
                    'button, input, textarea, select, a'
                )
                .forEach(element => {
                    element.draggable = false;
                });


            task.addEventListener(
                'dragstart',
                event => {

                    draggedTask = task;

                    sourceZone =
                        task.closest(
                            '[data-task-dropzone]'
                        );

                    task.dataset.wasDragged =
                        '1';

                    task.classList.add(
                        'opacity-50'
                    );

                    event.dataTransfer.effectAllowed =
                        'move';

                    event.dataTransfer.setData(
                        'text/plain',
                        task.dataset.taskId
                    );
                }
            );


            task.addEventListener(
                'dragend',
                () => {

                    task.classList.remove(
                        'opacity-50'
                    );

                    clearDropzones();

                    setTimeout(() => {
                        delete task.dataset.wasDragged;
                    }, 150);

                    draggedTask = null;
                    sourceZone = null;
                }
            );

        });


        dropzones.forEach(zone => {

            zone.addEventListener(
                'dragover',
                event => {

                    if (!draggedTask) {
                        return;
                    }

                    event.preventDefault();

                    event.dataTransfer.dropEffect =
                        'move';

                    clearDropzones();

                    zone.classList.add(
                        'ring-2',
                        'ring-indigo-400',
                        'ring-offset-2'
                    );
                }
            );


            zone.addEventListener(
                'dragleave',
                event => {

                    if (
                        zone.contains(
                            event.relatedTarget
                        )
                    ) {
                        return;
                    }

                    zone.classList.remove(
                        'ring-2',
                        'ring-indigo-400',
                        'ring-offset-2'
                    );
                }
            );


            zone.addEventListener(
                'drop',
                async event => {

                    event.preventDefault();

                    clearDropzones();


                    if (
                        !draggedTask ||
                        !sourceZone
                    ) {
                        return;
                    }


                    const taskId =
                        Number(
                            draggedTask
                                .dataset
                                .taskId
                        );

                    const sourceColumnId =
                        Number(
                            sourceZone
                                .dataset
                                .columnId
                        );

                    const targetColumnId =
                        Number(
                            zone
                                .dataset
                                .columnId
                        );


                    if (
                        !taskId ||
                        !targetColumnId ||
                        sourceColumnId
                            === targetColumnId
                    ) {
                        return;
                    }


                    draggedTask.classList.add(
                        'opacity-50'
                    );


                    try {

                        const response =
                            await fetch(
                                `${moveBase.content}/${taskId}/move`,
                                {
                                    method: 'PATCH',

                                    headers: {
                                        'X-CSRF-TOKEN':
                                            csrf.content,

                                        'Content-Type':
                                            'application/json',

                                        'Accept':
                                            'application/json',
                                    },

                                    body:
                                        JSON.stringify({
                                            target_column_id:
                                                targetColumnId,
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

                        zone.appendChild(
                            draggedTask
                        );


                        const issueCount =
                            document.getElementById(
                                'issues-count'
                            );

                        if (
                            issueCount &&
                            data.backlog_issue_count
                                !== undefined
                        ) {
                            issueCount.textContent =
                                data.backlog_issue_count;
                        }

                        window.location.reload();

                    } catch (error) {

                        console.error(
                            'Task move failed:',
                            error
                        );

                        draggedTask.classList.remove(
                            'opacity-50'
                        );

                    }

                }
            );

        });

    }
);