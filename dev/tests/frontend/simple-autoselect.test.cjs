const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const { readFileSync } = require('node:fs');
const path = require('node:path');
const { test } = require('node:test');
const vm = require('node:vm');

const root = path.resolve(__dirname, '../../..');

function templateScript(theme) {
    const html = execFileSync('php', [path.join(__dirname, 'render-autoselect.php'), theme], {
        encoding: 'utf8'
    });
    return html.match(/<script[^>]*>([\s\S]*?)<\/script>/)[1];
}

function control(name, type, values = []) {
    return {
        name,
        type,
        checked: false,
        value: '',
        options: values.map(value => ({ value, selected: false })),
        events: [],
        dispatchEvent(event) {
            this.events.push({ type: event.type, bubbles: event.bubbles });
        }
    };
}

function hyvaPage(hash, controls = []) {
    let initialized;
    const context = vm.createContext({
        URLSearchParams,
        Event,
        window: { location: { hash } },
        document: {
            querySelectorAll(selector) {
                assert.equal(selector, '#product_addtocart_form .product-custom-option');
                return controls;
            }
        },
        hyva: { alpineInitialized(callback) { initialized = callback; } }
    });
    vm.runInContext(templateScript('hyva'), context);
    return () => initialized();
}

test('Hyva simple pages initialize without RequireJS, jQuery, or custom options', () => {
    const initialize = hyvaPage('');
    assert.doesNotThrow(initialize);
});

test('Hyva waits for Alpine, then selects dropdown, multiselect, radio, and checkbox values', () => {
    const dropdown = control('options[12]', 'select-one', ['120', '121']);
    const multiple = control('options[13][]', 'select-multiple', ['130', '131']);
    multiple.options[0].selected = true;
    const radio = control('options[14]', 'radio');
    radio.value = '140';
    const checkbox = control('options[15][]', 'checkbox');
    checkbox.value = '150';
    const controls = [dropdown, multiple, radio, checkbox];
    const initialize = hyvaPage('#12=121&13=131&14=140&15=150', controls);

    assert.equal(dropdown.options[1].selected, false);
    assert.equal(radio.checked, false);
    initialize();

    assert.equal(dropdown.options[1].selected, true);
    assert.deepEqual(multiple.options.map(option => option.selected), [true, true]);
    assert.equal(radio.checked, true);
    assert.equal(checkbox.checked, true);
    controls.forEach(element => assert.deepEqual(element.events, [{ type: 'change', bubbles: true }]));
});

test('unknown, empty, malformed, and selector-like fragments preserve existing options', () => {
    const dropdown = control('options[12]', 'select-one', ['120']);
    dropdown.options[0].selected = true;
    const checkbox = control('options[15][]', 'checkbox');
    checkbox.value = '150';
    checkbox.checked = true;
    const text = control('options[16]', 'text');
    text.value = 'Keep this engraving';

    hyvaPage('#12=%22%5D&15=&16=replace&999=1&bad=%E0%A4%A', [dropdown, checkbox, text])();

    assert.equal(dropdown.options[0].selected, true);
    assert.equal(checkbox.checked, true);
    assert.equal(text.value, 'Keep this engraving');
    [dropdown, checkbox, text].forEach(element => assert.deepEqual(element.events, []));
});

test('only matching controls receive change events and numeric option parameters are decoded', () => {
    const matching = control('options[12]', 'select-one', ['120']);
    const unrelated = control('options[13]', 'select-one', ['130']);
    const sibling = control('options[14]', 'radio');
    sibling.value = '141';
    hyvaPage('#%31%32=%31%32%30&14=140', [matching, unrelated, sibling])();
    assert.equal(matching.options[0].selected, true);
    assert.equal(matching.events.length, 1);
    assert.deepEqual(unrelated.events, []);
    assert.deepEqual(sibling.events, []);
});

test('Luma initializes the widget under its registered jQuery plugin name', () => {
    const plugins = {};
    let selected = false;
    function $(target) {
        if (target === 'body') {
            return plugins;
        }
        return { ready(callback) { callback(); } };
    }
    $.mageos = {};
    $.widget = name => {
        const pluginName = name.split('.')[1];
        $.mageos[pluginName] = {};
        plugins[pluginName] = () => { selected = true; };
    };
    const context = vm.createContext({
        document: {},
        define(dependencies, factory) { factory($); },
        require(dependencies, callback) { callback($); }
    });
    vm.runInContext(readFileSync(path.join(root, 'view/frontend/web/js/autoselect/simple.js'), 'utf8'), context);
    vm.runInContext(templateScript('luma'), context);
    assert.equal(selected, true);
});
