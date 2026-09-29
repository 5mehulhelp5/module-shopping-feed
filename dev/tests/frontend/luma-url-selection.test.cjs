const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const { test } = require('node:test');
const vm = require('node:vm');

const root = path.resolve(__dirname, '../../..');

function fixture(hash, configurable = false) {
    const dropdown = { id: configurable ? 'attribute12' : 'select_12', name: 'super_attribute[12]', value: '', children: [
        { tag: 'option', value: '120', selected: false }, { tag: 'option', value: '121', selected: false }
    ] };
    const radio = { id: 'options-13-list', children: [{ tag: 'input', type: 'radio', value: '130', checked: false }] };
    const swatch = { 'attribute-id': '14', children: [{ tag: '.swatch-option', 'option-id': '140', events: [] }] };
    const nodes = [dropdown, radio, swatch];
    const selectors = [];
    function wrap(items) {
        return {
            length: items.length,
            each(callback) { items.forEach((item, index) => callback.call(item, index, item)); return this; },
            filter(callback) { return wrap(items.filter((item, index) => callback.call(item, index, item))); },
            find(selector) {
                selectors.push(selector);
                const match = selector.match(/^(option|input|\.swatch-option)?(?:\[(value|option-id)=["']?(\d+)["']?\])?$/);
                if (!match) throw new Error('Invalid selector: ' + selector);
                return wrap(items.flatMap(item => item.children || []).filter(item =>
                    (!match[1] || item.tag === match[1]) && (!match[2] || item[match[2]] === match[3])
                ));
            },
            prop(name, value) {
                if (value === undefined) return items[0]?.[name];
                items.forEach(item => { item[name] = value; }); return this;
            },
            attr(name) { return items[0]?.[name]; },
            val(value) {
                if (value === undefined) return items[0]?.value;
                items.forEach(item => { item.value = value; }); return this;
            },
            on() { return this; },
            first() { return wrap(items.slice(0, 1)); },
            trigger(event) { items.forEach(item => { (item.events ||= []).push(event); }); return this; }
        };
    }
    function $(target) {
        if (typeof target !== 'string') return wrap(target ? [target] : []);
        selectors.push(target);
        if (target === '.super-attribute-select') return wrap([dropdown]);
        if (target === '.swatch-attribute') return wrap([swatch]);
        if (/^#(?:select_\d+|options-\d+-list)$/.test(target)) return wrap(nodes.filter(n => n.id === target.slice(1)));
        throw new Error('Invalid selector: ' + target);
    }
    $.each = (values, callback) => Object.entries(values).forEach(([key, value]) => callback(key, value));
    $.proxy = (callback, self) => callback.bind(self);
    $.parseQuery = ({ query }) => Object.fromEntries(query.split('&').map(pair =>
        pair.split('=').map(value => decodeURIComponent(value.replace(/\+/g, ' ')))
    ));
    $.mageos = {};
    let widget;
    $.widget = (name, definition) => { widget = definition; };
    let exported;
    const context = vm.createContext({
        document: { getElementById(id) { return nodes.find(n => n.id === id) || null; } },
        window: { location: { hash, search: '' }, setTimeout(callback) { callback(); } },
        define(deps, factory) { exported = factory($); }
    });
    vm.runInContext(readFileSync(path.join(root, configurable
        ? 'view/frontend/web/js/configurable/selection.js'
        : 'view/frontend/web/js/autoselect/simple.js'), 'utf8'), context);
    return { dropdown, radio, swatch, selectors, run() {
        if (configurable) return new exported({ attributes: {}, index: {}, optionPrices: {} });
        widget.values = {};
        widget._parseQueryParams(hash.slice(1));
        widget._selectValuesByAttribute();
    } };
}

test('Luma simple selects matching dropdown and radio values', () => {
    const page = fixture('#12=121&13=130');
    page.run();
    assert.equal(page.dropdown.children[1].selected, true);
    assert.equal(page.radio.children[0].checked, true);
});

test('Luma simple ignores selector fragments and unknown values', () => {
    for (const hash of ['#12=%22%5D&13=%5B', '#12%2C%20%23other=121', '#12=999&13=', '#12=%E0%A4%A']) {
        const page = fixture(hash);
        assert.doesNotThrow(page.run);
        assert.equal(page.dropdown.children.some(o => o.selected), false);
        assert.equal(page.radio.children[0].checked, false);
    }
});

test('Luma configurable selects matching dropdown and swatch values', () => {
    const page = fixture('#12=121&14=140', true);
    page.run();
    assert.equal(page.dropdown.value, '121');
    assert.deepEqual(page.swatch.children[0].events, ['click']);
});

test('Luma configurable ignores selector fragments and malformed URL encoding', () => {
    for (const hash of ['#12=%22%5D&14=%22%5D', '#12=%E0%A4%A&14=999', '#12=999&14=']) {
        const page = fixture(hash, true);
        assert.doesNotThrow(page.run);
        assert.equal(page.dropdown.value, '');
        assert.deepEqual(page.swatch.children[0].events, []);
    }
});
