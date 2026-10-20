const assert = require('node:assert/strict');
const {mkdtempSync, writeFileSync, rmSync} = require('node:fs');
const {tmpdir} = require('node:os');
const {join, resolve} = require('node:path');
const {spawnSync} = require('node:child_process');
const {test} = require('node:test');

const check = resolve(__dirname, '../../../.github/scripts/check-audit-report.php');

function runWith(value) {
    const directory = mkdtempSync(join(tmpdir(), 'shopping-feed-audit-'));
    const path = join(directory, 'audit.json');
    writeFileSync(path, typeof value === 'string' ? value : JSON.stringify(value));
    try {
        return spawnSync('php', [check, path, 'test profile'], {encoding: 'utf8'});
    } finally {
        rmSync(directory, {recursive: true});
    }
}

const clean = () => ({advisories: [], abandoned: {'laminas/laminas-loader': null}, filter: []});

test('a clean locked audit passes and still reports abandoned packages', () => {
    const result = runWith(clean());
    assert.equal(result.status, 0, result.stderr);
    assert.match(result.stdout, /no security advisories/);
    assert.match(result.stdout, /laminas\/laminas-loader/);
});

for (const [name, edit] of [
    ['any advisory', r => { r.advisories = {'vendor/package': [{advisoryId: 'PKSA-test'}]}; }],
    ['ignored advisories', r => { r['ignored-advisories'] = {'vendor/package': [{advisoryId: 'PKSA-test'}]}; }],
    ['filter-list matches', r => { r.filter = {'vendor/package': [{listName: 'malware'}]}; }],
    ['malware findings', r => { r.malware = {'vendor/package': [{}]}; }],
    ['unreachable repositories', r => { r['unreachable-repositories'] = ['https://repo.example']; }],
    ['a malformed advisory group', r => { r.advisories = {'vendor/package': 'invalid'}; }],
    ['an incomplete report', r => { delete r.advisories; }]
]) {
    test(`the audit check fails closed for ${name}`, () => {
        const fixture = clean();
        edit(fixture);
        assert.notEqual(runWith(fixture).status, 0);
    });
}

test('the audit check rejects a report that is not JSON', () => {
    assert.notEqual(runWith('Found 1 security vulnerability advisory').status, 0);
});
