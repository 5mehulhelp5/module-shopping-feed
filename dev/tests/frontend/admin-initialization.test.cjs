const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const { test } = require('node:test');
const vm = require('node:vm');

const root = resolve(__dirname, '../../..');

test('Category mapping initializes with only its declared dependencies and serializes before save', () => {
    let widget, beforeSubmit, saved;
    const empty = { on() { return this; }, trigger() { return this; }, hide() {} };
    const form = { on(name, handler) { if (name === 'beforeSubmit') beforeSubmit = handler; } };
    const $ = selector => {
        if (selector === '#edit_form') return form;
        if (selector === '#taxonomy_aggregated_values') return { val(value) { saved = value; } };
        return empty;
    };
    $.mageos = {};
    $.widget = (name, definition) => { widget = definition; };
    const taxonomy = { 3: { tx: 'Furniture', d: 1 } };
    vm.runInNewContext(readFileSync(resolve(root, 'view/adminhtml/web/js/category-taxonomy.js'), 'utf8'), {
        define(dependencies, factory) {
            if (dependencies.includes('mage/backend/form')) form.form = () => form;
            factory($);
        },
        taxonomy
    });
    widget._create.call({ element: { find: () => empty }, ...widget });
    assert.equal(typeof beforeSubmit, 'function');
    beforeSubmit();
    assert.deepEqual(JSON.parse(saved), taxonomy);
});

test('Promotion counter works without a global Prototype alias and increments only once', () => {
    const template = readFileSync(resolve(root, 'view/adminhtml/templates/feed/edit/tab/promotions/widget.phtml'), 'utf8');
    const script = template.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/<\?(?:php|=)[\s\S]*?\?>/g, 'date-format');
    let click;
    const input = { value: '4' };
    const classes = new Set();
    const button = {
        nextElementSibling: input,
        classList: { add: name => classes.add(name) },
        addEventListener(name, handler) { if (name === 'click') click = handler; }
    };
    const success = { classList: { add: name => classes.add(name) } };
    vm.runInNewContext(script, {
        document: { getElementById: id => id === 'increment-counter' ? button : success }
    });
    assert.equal(typeof click, 'function');
    click.call(button);
    click.call(button);
    assert.equal(Number(input.value), 5);
    assert.ok(classes.has('disabled'));
    assert.ok(classes.has('visible'));
});
