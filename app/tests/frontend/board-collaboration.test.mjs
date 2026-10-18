import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

// Small DOM doubles keep these behavioural checks dependency-free.
function columnHarness({ editable = true, version = 1 } = {}) {
    const listeners = {};
    const board = { dataset: { boardId: '7', boardVersion: String(version), canEdit: String(editable) } };
    const marker = {};
    const elements = [1, 2, 3].map(id => ({ dataset: { columnId: String(id) } }));
    const list = {
        children: [...elements, marker],
        querySelectorAll() { return this.children.filter(item => item !== marker); },
        querySelector(selector) {
            const id = selector.match(/column-id="(\d+)"/)?.[1];
            return this.children.find(item => item.dataset?.columnId === id) ?? null;
        },
        insertBefore(item, target) {
            this.children = this.children.filter(candidate => candidate !== item);
            const index = target ? this.children.indexOf(target) : this.children.length;
            this.children.splice(index, 0, item);
        },
        addEventListener(event, handler) { listeners[event] = handler; },
    };
    let sortableOptions;
    let request;
    const context = vm.createContext({
        document: {
            querySelector: selector => selector.includes('task-sync-context') ? board : null,
            getElementById: id => id === 'columns-container' ? list : marker,
            addEventListener: (event, handler) => { listeners[event] = handler; },
        },
        Sortable: class { constructor(element, options) { sortableOptions = options; } option() {} },
        jsonRequest: async (url, options) => { request = { url, ...options }; return {
            payload: { entity: { id: 7, version: 2 }, meta: { column_order: options.body.column_ids } },
        }; },
    });
    const source = readFileSync(new URL('../../resources/js/column-dnd.js', import.meta.url), 'utf8')
        .replace(/^import .*;\n/gm, '').replace('export function applyColumnOrder', 'function applyColumnOrder');
    vm.runInContext(source, context);
    return { context, board, list, elements, listeners, options: () => sortableOptions, request: () => request };
}

test('remote column order keeps the original elements and all their task context', () => {
    const h = columnHarness();
    h.elements[0].taskContext = { task: 'Keep me' };
    h.context.applyColumnOrder({ entity: { id: 7, version: 2 }, meta: { column_order: [3, 1, 2] } });
    assert.deepEqual(h.list.querySelectorAll().map(item => item.dataset.columnId), ['3', '1', '2']);
    assert.equal(h.list.querySelectorAll()[1], h.elements[0]);
    assert.deepEqual(h.elements[0].taskContext, { task: 'Keep me' });
    assert.equal(h.board.dataset.boardVersion, 2);
});

test('an old remote order cannot overwrite a newer order', () => {
    const h = columnHarness({ version: 4 });
    h.context.applyColumnOrder({ entity: { id: 7, version: 3 }, meta: { column_order: [3, 2, 1] } });
    assert.deepEqual(h.list.querySelectorAll().map(item => item.dataset.columnId), ['1', '2', '3']);
});

test('an update for another board cannot reorder the current board', () => {
    const h = columnHarness();
    h.context.applyColumnOrder({ entity: { id: 99, version: 2 }, meta: { column_order: [3, 2, 1] } });
    assert.deepEqual(h.list.querySelectorAll().map(item => item.dataset.columnId), ['1', '2', '3']);
});

test('observers never receive an interactive column Sortable instance', () => {
    const h = columnHarness({ editable: false });
    h.listeners.DOMContentLoaded();
    assert.equal(h.options(), undefined);
});

test('a column drop sends all column IDs and the board version', async () => {
    const h = columnHarness();
    h.listeners.DOMContentLoaded();
    h.options().onStart();
    h.list.insertBefore(h.elements[2], h.elements[0]);
    h.options().onEnd({ oldDraggableIndex: 2, newDraggableIndex: 0 });
    await new Promise(resolve => setTimeout(resolve, 0));
    assert.equal(h.request().url, '/boards/7/columns/order');
    assert.equal(h.request().method, 'PATCH');
    assert.deepEqual(Array.from(h.request().body.column_ids), [3, 1, 2]);
    assert.equal(h.request().body.expected_version, 1);
    assert.deepEqual(Array.from(h.request().body.expected_order), [1, 2, 3]);
});

test('task clicks inside the detail form cannot reopen the modal', () => {
    const source = readFileSync(new URL('../../resources/js/task-ui.js', import.meta.url), 'utf8');
    const start = source.indexOf("        const task = event.target.closest('[data-task-id]');");
    const end = source.indexOf("        if (!event.target.closest('[data-task-dropdown]", start);
    const handler = source.slice(start, end);
    let reopened = false;
    vm.runInNewContext(`(() => { ${handler} })()`, {
        event: { target: { closest: selector => ['[data-task-id]', '[data-task-detail]'].includes(selector)
            ? { dataset: { taskId: '1' } } : null } },
        openTask: () => { reopened = true; },
    });
    assert.equal(reopened, false);
});

test('column updates from another joined board never appear on the current board', async () => {
    const source = readFileSync(new URL('../../resources/js/realtime-reconciler.js', import.meta.url), 'utf8');
    const start = source.indexOf('async function upsertColumn(');
    const end = source.indexOf('function deleteColumn(', start);
    let fetched = false;
    await vm.runInNewContext(`${source.slice(start, end)}; upsertColumn({ entity: { id: 99, board_id: 8 } }, 'column.created')`, {
        context: () => 'board', root: () => ({ dataset: { boardId: '7' } }),
        document: { getElementById: () => null },
        fetchColumn: async () => { fetched = true; throw new Error('Wrong board fetched'); },
    });
    assert.equal(fetched, false);
});
