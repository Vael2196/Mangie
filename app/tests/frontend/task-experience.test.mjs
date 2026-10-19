import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';
import { edgeColor } from '../../resources/js/task-cover-colors.js';

function menuHarness({ canEdit = true } = {}) {
    const listeners = {};
    const requests = [];
    class Element {
        constructor(tag) { this.tagName = tag; this.children = []; this.events = {}; this.attrs = {}; this.dataset = {}; this.style = {}; this.hidden = false; this.classList = { add() {}, toggle() {} }; }
        append(...nodes) { this.children.push(...nodes); }
        replaceChildren(...nodes) { this.children = nodes; }
        setAttribute(k, v) { this.attrs[k] = v; }
        removeAttribute(k) { delete this.attrs[k]; }
        addEventListener(k, f) { this.events[k] = f; }
        getBoundingClientRect() { return { width: 256, height: 350 }; }
        remove() { this.removed = true; }
        focus() { this.focused = true; }
        querySelector(selector) { return this.children.find(c => c.tagName === selector) ?? this.children.flatMap(c => c.children ?? []).find(c => c.tagName === selector); }
    }
    const doc = { createElement: tag => new Element(tag), createTextNode: text => ({ textContent: text }), body: new Element('body'), addEventListener: (event, handler) => { listeners[event] = handler; }, querySelector: () => null };
    const context = vm.createContext({
        document: doc,
        window: { innerWidth: 375, innerHeight: 667, setTimeout() {}, addEventListener() {} },
        jsonRequest: async (url, options) => {
            requests.push({ url, ...options });
            if (!options) return { task: { id: 9, title: 'Test task', version: 4, column_id: 1 }, can_edit: canEdit, joined: false, watching: false, columns: [{ id: 1, name: 'TO DO' }, { id: 2, name: 'DOING' }] };
            return options.method === 'PUT' ? { watching: options.body.watching } : { payload: { entity: { id: 9 } } };
        },
        applyMutationResponse: async () => {}, openTask: async () => {},
    });
    const source = readFileSync(new URL('../../resources/js/task-actions.js', import.meta.url), 'utf8').replace(/^import .*;\n/gm, '').replaceAll('export function ', 'function ');
    vm.runInContext(source, context);
    return { context, doc, listeners, requests, menu: () => vm.runInContext('activeMenu.element', context) };
}

function label(node) { return node.children.map(child => child.textContent ?? '').join(''); }
function option(menu, text) { return menu.children.find(child => child.tagName === 'button' && label(child).includes(text)); }

test('task menus clamp to a small viewport in both directions', () => {
    const h = menuHarness();
    const p = h.context.menuPosition(360, 650, 256, 350, 375, 667);
    assert.equal(p.left, 111); assert.equal(p.top, 309);
    assert.equal(h.context.menuPosition(-100, -2, 256, 350, 375, 667).left, 8);
});

test('observers get Details and Watch but no mutation controls', async () => {
    const h = menuHarness({ canEdit: false });
    await h.context.showMenu(9, { x: 10, y: 10 });
    const labels = h.menu().children.filter(node => node.tagName === 'button').map(label);
    assert.equal(labels.length, 2);
    assert.ok(labels.some(text => text.includes('Details')));
    assert.ok(labels.some(text => text.includes('Watch')));
});

test('join uses the additive quick action and includes the current version', async () => {
    const h = menuHarness();
    await h.context.showMenu(9, { x: 10, y: 10 });
    option(h.menu(), 'Join').events.click({ stopPropagation() {} });
    await new Promise(resolve => setTimeout(resolve, 0));
    const request = h.requests.at(-1);
    assert.equal(request.url, '/tasks/9/actions');
    assert.equal(request.body.joined, true);
    assert.equal(request.body.expected_version, 4);
});

test('watch is a personal checkbox and does not submit task details', async () => {
    const h = menuHarness({ canEdit: false });
    await h.context.showMenu(9, { x: 10, y: 10 });
    const watch = option(h.menu(), 'Watch');
    watch.events.click({ stopPropagation() {} });
    await new Promise(resolve => setTimeout(resolve, 0));
    assert.equal(h.requests.at(-1).url, '/tasks/9/watch');
    assert.equal(h.requests.at(-1).body.watching, true);
    assert.equal(watch.attrs['aria-checked'], 'true');
});

test('cover colour samples the edge and ignores a different centre colour', () => {
    const pixels = [];
    for (let i = 0; i < 9; i++) pixels.push(...(i === 4 ? [255, 0, 0, 255] : [20, 40, 80, 255]));
    assert.equal(edgeColor(pixels, 3, 3), 'rgb(20, 40, 80)');
    assert.equal(edgeColor(new Uint8Array(16), 2, 2), null);
});
