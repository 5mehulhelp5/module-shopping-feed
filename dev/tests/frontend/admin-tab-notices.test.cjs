const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const { test } = require('node:test');
const vm = require('node:vm');

const root = resolve(__dirname, '../../..');

for (const [name, href, dataTarget, index, expected, idOnTab] of [
    ['sanitized notice link', '#feed_tabs_filters', undefined, 4, 4],
    ['legacy data link', '#', '#feed_tabs_columns', 2, 2],
    ['external link', 'https://example.com/', undefined, 4, null],
    ['missing tab', '#feed_tabs_missing', undefined, -1, null],
    ['theme repeats target ID on the tab container', '#feed_tabs_filters', undefined, 4, 4, true],
]) {
    test(`Feed tab navigation handles ${name}`, () => {
        let widget, active = null, prevented = false;
        const link = {};
        const $ = selector => {
            if (selector === link) return { data: () => dataTarget, attr: () => href };
            if (selector === '#feed_tabs') return { tabs: options => { active = options.active; } };
            if (selector.endsWith(' li')) return { index: node => node.isTab ? index : -1 };
            return { find: () => ({
                parent: () => ({ isTab: !idOnTab }),
                closest: () => ({ isTab: true })
            }) };
        };
        $.mageos = {};
        $.widget = (name, definition) => { widget = definition; };
        vm.runInNewContext(readFileSync(resolve(root, 'view/adminhtml/web/js/feed-form.js'), 'utf8'), {
            define(dependencies, factory) { factory($); }
        });
        widget._switchTabs.call(link, { preventDefault() { prevented = true; } });
        assert.equal(active, expected);
        assert.equal(prevented, expected !== null);
    });
}
