const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const { test } = require('node:test');
const vm = require('node:vm');

test('Find/replace initialization preserves literal backslashes, quotes and HTML', () => {
    const template = readFileSync(resolve(__dirname, '../../../view/adminhtml/templates/feed/edit/tab/filters/find-replace.phtml'), 'utf8');
    const body = template.match(/addItem\s*:\s*function\s*\(\)\s*\{([\s\S]*?)\n    \},\n    disableElement/)[1]
        .replace(/<\?php if \(\$readonly\): \?>[\s\S]*?<\?php endif; \?>/g, '')
        .replace(/<\?(?:php|=)[\s\S]*?\?>/g, 'fixture');
    let row;
    const control = { itemsCount: 0, template({ data }) { row = data; return ''; } };
    const addItem = vm.runInNewContext(`(function () { ${body} })`, {
        $: () => ({ value: '' }), Element: { insert() {} }
    });
    const literal = "\\' </script> & \"\n";
    addItem.call(control, literal, literal, 'description', 0);
    assert.equal(row.find, literal);
    assert.equal(row.replace, literal);
    assert.equal(row.column, 'description');
});
