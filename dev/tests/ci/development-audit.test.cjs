const assert = require('node:assert/strict');
const {mkdtempSync, writeFileSync, rmSync} = require('node:fs');
const {tmpdir} = require('node:os');
const {join, resolve} = require('node:path');
const {spawnSync} = require('node:child_process');
const {test} = require('node:test');

const script = resolve(__dirname, '../../../.github/scripts/check-development-audit.php');
const legacyLock = () => ({packages: [], 'packages-dev': [
    {name: 'magento/magento2-base', version: '2.4.7-p10'},
    {name: 'magento/framework', version: '103.0.7-p10'},
    {name: 'league/flysystem', version: '2.5.0'}
]});
const advisory = () => ({advisories: {'league/flysystem': [{advisoryId: 'PKSA-w9tt-7782-78jx'}]}});

function run(report, lock = legacyLock(), profile = '8.1') {
    const directory = mkdtempSync(join(tmpdir(), 'shopping-feed-development-audit-'));
    const reportPath = join(directory, 'audit.json');
    const lockPath = join(directory, 'composer.lock');
    writeFileSync(reportPath, JSON.stringify(report));
    writeFileSync(lockPath, JSON.stringify(lock));
    try {
        return spawnSync('php', [script, reportPath, lockPath, profile], {encoding: 'utf8'});
    } finally {
        rmSync(directory, {recursive: true});
    }
}

test('the exact PHP 8.1 legacy tool graph retains its known advisory', () => {
    const result = run(advisory());
    assert.equal(result.status, 0, result.stderr);
    assert.match(result.stdout, /Known upstream advisory remains visible/);
    assert.match(result.stdout, /Compatibility testing is not platform security approval/);
});

for (const packageName of ['magento/magento2-base', 'magento/framework', 'league/flysystem']) {
    for (const change of ['version', 'missing', 'duplicate']) {
        test(`the exception rejects ${change} ${packageName}`, () => {
            const lock = legacyLock();
            const index = lock['packages-dev'].findIndex(p => p.name === packageName);
            if (change === 'version') lock['packages-dev'][index].version = '99.0.0';
            if (change === 'missing') lock['packages-dev'].splice(index, 1);
            if (change === 'duplicate') lock.packages.push({...lock['packages-dev'][index]});
            assert.notEqual(run(advisory(), lock).status, 0);
        });
    }
}

test('another PHP profile retains strict auditing', () => {
    assert.notEqual(run(advisory(), legacyLock(), '8.5').status, 0);
    assert.equal(run({advisories: {}}, {packages: []}, '8.5').status, 0);
});

for (const [name, edit] of [
    ['another advisory', r => {r.advisories.other = [{advisoryId: 'PKSA-unrelated'}];}],
    ['hidden advisories', r => {r['ignored-advisories'] = {other: ['hidden']};}],
    ['malware', r => {r.malware = ['bad'];}],
    ['filter matches', r => {r.filter = ['bad'];}],
    ['unreachable repositories', r => {r['unreachable-repositories'] = ['offline'];}],
    ['a malformed advisory group', r => {r.advisories.other = 'invalid';}],
    ['an absent advisory', r => {r.advisories = {};}]
]) {
    test(`the legacy profile fails closed for ${name}`, () => {
        const report = advisory(); edit(report);
        assert.notEqual(run(report).status, 0);
    });
}
