const assert = require('node:assert/strict');
const {mkdtempSync, writeFileSync, readFileSync, rmSync} = require('node:fs');
const {tmpdir} = require('node:os');
const {join, resolve} = require('node:path');
const {spawnSync} = require('node:child_process');
const {test} = require('node:test');

const configure = resolve(__dirname, '../../../.github/scripts/configure-magento-247-compat.php');
const audit = resolve(__dirname, '../../../.github/scripts/check-magento-247-audit.php');
const advisory = 'PKSA-w9tt-7782-78jx';

function withJson(value, callback) {
    const directory = mkdtempSync(join(tmpdir(), 'shopping-feed-247-policy-'));
    const path = join(directory, 'input.json');
    writeFileSync(path, JSON.stringify(value));
    try { callback(path); } finally { rmSync(directory, {recursive: true}); }
}

function run(script, path) {
    return spawnSync('php', [script, path], {encoding: 'utf8'});
}

const project = () => ({
    name: 'magento/project-community-edition',
    require: {'magento/product-community-edition': '2.4.7-p10'},
    config: {'allow-plugins': {'magento/magento-composer-installer': true}}
});
const report = () => ({advisories: {'league/flysystem': [{advisoryId: advisory}]}, abandoned: {}});

test('only the exact disposable platform gets a blocking-only exception', () => {
    withJson(project(), path => {
        const result = run(configure, path);
        assert.equal(result.status, 0, result.stderr);
        const config = JSON.parse(readFileSync(path));
        assert.equal(config.config.audit.ignore[advisory].apply, 'block');
        assert.deepEqual(Object.keys(config.config.audit.ignore), [advisory]);
        assert.equal(config.config.audit['block-insecure'], true);
        assert.equal(config.config['allow-plugins']['magento/magento-composer-installer'], true);
        assert.equal(run(configure, path).status, 0, 'configuration is idempotent');
    });
});

for (const [name, edit] of [
    ['another Magento version', p => { p.require['magento/product-community-edition'] = '2.4.8'; }],
    ['the distributed extension', p => { p.name = 'mage-os/module-shopping-feed'; }],
    ['broad audit bypass', p => { p.config.audit = {'block-insecure': false}; }],
    ['policy override', p => { p.config.policy = false; }],
    ['another advisory exception', p => { p.config.audit = {ignore: ['PKSA-unrelated']}; }]
]) {
    test(`configuration rejects ${name} without changing the file`, () => {
        const fixture = project(); edit(fixture);
        withJson(fixture, path => {
            const before = readFileSync(path, 'utf8');
            assert.notEqual(run(configure, path).status, 0);
            assert.equal(readFileSync(path, 'utf8'), before);
        });
    });
}

test('audit retains and identifies the one expected advisory', () => {
    withJson(report(), path => assert.equal(run(audit, path).status, 0));
});

for (const [name, edit] of [
    ['an additional advisory', r => { r.advisories['league/flysystem'].push({advisoryId: 'PKSA-unrelated'}); }],
    ['the known ID on another package', r => { r.advisories.other = r.advisories['league/flysystem']; delete r.advisories['league/flysystem']; }],
    ['a hidden or resolved advisory', r => { r.advisories = {}; }],
    ['ignored advisories', r => { r['ignored-advisories'] = {other: [{advisoryId: 'PKSA-unrelated'}]}; }],
    ['a malformed advisory group', r => { r.advisories.other = 'invalid'; }],
    ['an incomplete report', r => { delete r.advisories; }]
]) {
    test(`audit fails closed for ${name}`, () => {
        const fixture = report(); edit(fixture);
        withJson(fixture, path => assert.notEqual(run(audit, path).status, 0));
    });
}
