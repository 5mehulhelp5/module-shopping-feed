const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const { test } = require('node:test');
const vm = require('node:vm');

const template = readFileSync(resolve(__dirname, '../../../view/adminhtml/templates/feed/edit/tab/columns/columns-map.phtml'), 'utf8');
const start = template.indexOf('preselectParamsColumn : function (data) {');
const end = template.indexOf('\n};', start);
const method = template.slice(start, end).replace(/<\?php[\s\S]*?\?>/g, 'columns');

function selectParameters(values, available) {
    const select = { options: available.map(value => ({ value, selected: false })), add(option) { this.options.push(option); } };
    const column = { select(selector) { return selector === 'select[multiple]' ? [select] : select.options; } };
    const context = vm.createContext({
        $: () => column,
        Option: function (text, value) { this.text = text; this.value = value; this.selected = false; },
        Array,
        values,
        select,
    });
    vm.runInContext(`const control = {${method}}; control.preselectParamsColumn({index: 0, param: values});`, context);
    return select.options.filter(option => option.selected).map(option => option.value);
}

test('Admin preserves missing optional attribute mappings across a form save', () => {
    assert.deepEqual(selectParameters(['pattern'], ['color', 'size', 'material']), ['pattern']);
});

test('Admin preserves both available and missing selected attributes without duplicates', () => {
    assert.deepEqual(selectParameters(['material', 'gender', 'gender'], ['material']), ['material', 'gender']);
});

test('Admin treats unavailable parameter text as a literal option value', () => {
    const value = '<img src=x onerror=alert(1)>';
    assert.deepEqual(selectParameters([value], ['material']), [value]);
});
