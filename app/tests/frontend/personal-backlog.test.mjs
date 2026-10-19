import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/js/realtime-reconciler.js', import.meta.url), 'utf8');

test('a realtime count from another backlog cannot overwrite the current backlog count', () => {
    const body = source.slice(source.indexOf('function applyCounts('), source.indexOf('async function upsertTask('));
    let count = 4;
    const context = vm.createContext({
        root: () => ({ dataset: { backlogBoardId: '17' } }),
        setColumnCount() {}, setIssueCount: value => { count = value; },
    });
    vm.runInContext(body, context);
    context.applyCounts({ backlog_board_id: 1, backlog_issue_count: 999 });
    assert.equal(count, 4);
    context.applyCounts({ backlog_board_id: 17, backlog_issue_count: 5 });
    assert.equal(count, 5);
    context.applyCounts({ column_id: 7, column_count: 3 });
    assert.equal(count, 5);
});

test('backlogs never enter the realtime home or dashboard sprint list', () => {
    const body = source.slice(source.indexOf('function matchesBoardListFilters('), source.indexOf('function updateBoardListEmptyState('));
    const context = vm.createContext({
        root: () => ({ dataset: { boardFilterShowInactive: '1', boardFilterOwned: '1', currentUserId: '7' } }),
        context: () => 'boards',
    });
    vm.runInContext(body, context);
    assert.equal(context.matchesBoardListFilters({ is_backlog: true, owner_id: 7 }), false);
    assert.equal(context.matchesBoardListFilters({ is_backlog: false, owner_id: 7 }), true);
});

test('backlog board updates cannot append a sprint preview in backlog planning', async () => {
    const body = source.slice(source.indexOf('async function applyBoardMutation('), source.indexOf('export async function applyMutation('));
    await vm.runInNewContext(`${body}; applyBoardMutation('board.updated', { entity: { id: 17, is_backlog: true }, meta: {} })`, {
        context: () => 'backlog',
        document: { getElementById() { throw new Error('A backlog was rendered as a sprint'); } },
    });
});

test('the drag-and-drop response also scopes the issue counter to the visible backlog', () => {
    const dnd = readFileSync(new URL('../../resources/js/task-dnd.js', import.meta.url), 'utf8');
    const body = dnd.slice(dnd.indexOf('function updateCounts('), dnd.indexOf('export function insertAtPosition('));
    let count = 4;
    const context = vm.createContext({
        document: { querySelector: () => ({ dataset: { backlogBoardId: '17' } }) },
        setColumnCount() {}, setIssueCount: value => { count = value; },
    });
    vm.runInContext(body, context);
    context.updateCounts({ backlog_board_id: 1, backlog_issue_count: 99 });
    assert.equal(count, 4);
    context.updateCounts({ backlog_board_id: 17, backlog_issue_count: 3 });
    assert.equal(count, 3);
});
