const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '../../..');
function load(name, dependencies = []) {
    let result;
    vm.runInNewContext(fs.readFileSync(path.join(root, 'view/adminhtml/web/js/form', name + '.js'), 'utf8'), {
        define: (_, factory) => { result = factory(...dependencies); }
    });
    return result;
}
const plain = value => JSON.parse(JSON.stringify(value));
const observable = initial => {
    let value = initial;
    return function (next) { if (arguments.length) { value = next; } return value; };
};
const base = {extend: definition => definition};

test('selects preserve missing scalar, zero and array options without interpreting HTML', () => {
    const preserve = load('preserve-options');
    const options = [{label: 'Attributes', value: [{value: 'sku', label: 'SKU'}]}];
    const text = '<img src=x onerror=alert(1)>';
    const result = preserve(options, ['sku', '0', text, text]);
    assert.equal(result.length, 3);
    assert.equal(result[2].label, text);
    assert.equal(options.length, 1);
    assert.deepEqual(plain(preserve([], ['constructor', '__proto__'])).map(option => option.value),
        ['constructor', '__proto__']);
});

test('opening a custom parameter keeps its exact structure; explicit directive change applies defaults', () => {
    const component = load('parameter', [base, load('preserve-options')]);
    const param = {zero: '0', values: ['missing', '<b>literal</b>']};
    const field = Object.assign({}, component, {
        definitions: {custom: {kind: 'unsupported'}, price: {kind: 'select', default: '0', options: [{value: '0', label: 'No'}]}},
        value: observable(param), kind: observable(''), parameterLabel: observable(''),
        parameterNotice: observable(''), parameterOptions: observable([])
    });
    field.changeAttribute('custom');
    assert.deepEqual(field.value(), param);
    assert.deepEqual(plain(field.normalizeData([])), []);
    assert.equal(field.normalizeData(false), false);
    assert.equal(field.normalizeData(0), 0);
    field.changeAttribute('price');
    assert.equal(field.value(), '0');
    field.value('1');
    field.changeAttribute('price');
    assert.equal(field.value(), '1');
});

test('parameter initialization preserves null defaults and explicit empty values', () => {
    const component = load('parameter', [base, load('preserve-options')]);
    for (const value of [null, '', '0', 0, false, ['literal']]) {
        const field = Object.assign({}, component, {value: observable(value), default: 'replacement'});
        assert.equal(field.getInitialValue(), value);
    }
    const missing = Object.assign({}, component, {value: observable(undefined), default: 'kg'});
    assert.equal(missing.getInitialValue(), 'kg');
});

test('provider submits empty arrays and literal nested parameters in a lossless envelope', () => {
    const provider = load('provider', [base]);
    const data = {config: {columns_product_columns: [], custom: {param: ['0', false, '\\d+ <x>']}}, uploads: []};
    let sent;
    provider.get = () => data;
    provider.childIds = {};
    provider.client = {save: (payload, options) => { sent = {payload, options}; }};
    provider.save({redirect: false});
    assert.deepEqual(JSON.parse(sent.payload.feed_form_data), data);
    assert.equal(sent.options.redirect, false);
});

test('provider marks removed persisted children for deletion without modifying the live form data', () => {
    const provider = load('provider', [base]);
    const data = {config: {}, schedules: [{id: '1'}, {id: ''}], uploads: [{id: '9'}]};
    Object.assign(provider, {_super: () => {}, get: () => data});
    provider.initialize();
    data.schedules = [];
    data.uploads.push({id: '', host: 'new.example'});
    let sent;
    provider.client = {save: payload => { sent = JSON.parse(payload.feed_form_data); }};
    provider.save({});
    assert.deepEqual(sent.schedules, [{id: '1', delete: true}]);
    assert.deepEqual(sent.uploads, data.uploads);
    assert.deepEqual(data.schedules, []);
});

test('provider decodes literal initial data after UI template initialization', () => {
    const provider = load('provider', [base]);
    const original = {config: {param: {nested: ['${ literal }', '<b>text</b>'], flag: false}}, uploads: [{id: 9}]};
    let data = {feed_form_initial_data: JSON.stringify(original)};
    Object.assign(provider, {_super: () => {}, get: () => data, set: (key, value) => { data = value; }});
    provider.initialize();
    assert.deepEqual(plain(data), original);
    assert.deepEqual(plain(provider.childIds.uploads), [9]);
});

test('new mapping rows append after existing priorities without renumbering duplicate orders', () => {
    const component = load('ordered-rows', [base]);
    const rows = [{order: 10}, {order: 10}, {order: 80}];
    let added, assigned;
    Object.assign(component, {
        recordData: () => rows, dataScope: 'data.config', index: 'columns_product_columns',
        source: {set: (path, value) => { assigned = {path, value}; }},
        _super: (data, index) => { added = index; }
    });
    component.addChild({}, false);
    assert.equal(added, 3);
    assert.deepEqual(assigned, {path: 'data.config.columns_product_columns.3.order', value: 90});
    assigned = null;
    component.addChild({}, 0);
    assert.equal(assigned, null);
    assert.deepEqual(rows, [{order: 10}, {order: 10}, {order: 80}]);
});

test('promotion counter increments only once until the next editor load', () => {
    const counter = load('promotion-counter', [base]);
    Object.assign(counter, {value: observable('4'), disabled: observable(false), incremented: observable(false)});
    counter.increment(); counter.increment();
    assert.equal(counter.value(), 5);
    assert.equal(counter.incremented(), true);
});

test('category edits preserve zero priorities, inactive and unknown mappings, and inherit into empty descendants', () => {
    function subscribed(initial) {
        let value = initial;
        const subscribers = [];
        const result = function (next) {
            if (arguments.length && next !== value) {
                value = next;
                subscribers.forEach(callback => callback(next));
            }
            return value;
        };
        result.subscribe = callback => subscribers.push(callback);
        return result;
    }
    const component = load('categories', [base, {observable: subscribed}]);
    const field = Object.assign({}, component, {
        _super: () => {}, disabled: observable(false),
        categories: [
            {id: 2, path: '1/2', store_active: '1'},
            {id: 3, path: '1/2/3', store_active: '1'},
            {id: 4, path: '1/2/4', store_active: '0'}
        ],
        value: observable({2: {p: 0}, 3: {ty: 'Keep this'}, 4: {d: 0}, 99: {tx: 'Unknown category'}})
    });
    field.initialize();
    assert.equal(field.rows[0].priority(), 0);
    field.rows[0].taxonomy('Office > Paper');
    field.rows[0].productType('Parent type');
    field.disableAll();
    const mapping = plain(field.value());
    assert.equal(mapping[2].id, 2);
    assert.equal(mapping[3].id, 3);
    assert.equal(mapping[3].tx, 'Office > Paper');
    assert.equal(mapping[3].ty, 'Keep this');
    assert.equal(mapping[2].d, 0);
    assert.deepEqual(mapping[4], {d: 0});
    assert.deepEqual(mapping[99], {tx: 'Unknown category'});
    field.collapseAll();
    assert.equal(field.rows[0].shown(), true);
    assert.equal(field.rows[1].shown(), false);
});
