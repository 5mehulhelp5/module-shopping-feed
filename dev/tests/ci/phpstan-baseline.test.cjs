const assert = require('node:assert/strict');
const {mkdtempSync, writeFileSync, rmSync} = require('node:fs');
const {tmpdir} = require('node:os');
const {join, resolve} = require('node:path');
const {spawnSync} = require('node:child_process');
const {test} = require('node:test');

const guard = resolve(__dirname, '../check-phpstan-baseline.php');

function entry(message, identifier, count, path) {
    return `\t\t-\n\t\t\tmessage: '${message}'\n\t\t\tidentifier: ${identifier}\n\t\t\tcount: ${count}\n\t\t\tpath: ${path}\n\n`;
}

function baseline(...entries) {
    return `parameters:\n\tignoreErrors:\n${entries.join('')}`;
}

function compare(reference, current) {
    const directory = mkdtempSync(join(tmpdir(), 'shopping-feed-baseline-'));
    try {
        writeFileSync(join(directory, 'reference.neon'), reference);
        writeFileSync(join(directory, 'current.neon'), current);
        return spawnSync('php', [guard, join(directory, 'reference.neon'), join(directory, 'current.neon')], {
            encoding: 'utf8'
        });
    } finally {
        rmSync(directory, {recursive: true});
    }
}

const quoted = '#^Parameter \\$x of method A\\:\\:b\\(\\) has invalid type O\'\'Brien\\.$#';
const reference = baseline(
    entry(quoted, 'argument.type', 2, 'Model/Feed.php'),
    entry('#^Unused\\.$#', 'missingType.return', 1, 'Model/Generator.php')
);

test('an unchanged baseline passes', () => {
    const result = compare(reference, reference);
    assert.equal(result.status, 0, result.stderr);
    assert.match(result.stdout, /did not grow/);
});

test('removing entries or lowering counts passes', () => {
    const result = compare(reference, baseline(entry(quoted, 'argument.type', 1, 'Model/Feed.php')));
    assert.equal(result.status, 0, result.stderr);
    assert.match(result.stdout, /Entries removed: 1/);
});

for (const [name, current] of [
    ['a raised count', baseline(entry(quoted, 'argument.type', 3, 'Model/Feed.php'))],
    ['a new message', baseline(entry('#^Another\\.$#', 'argument.type', 1, 'Model/Feed.php'))],
    ['the same message in another file', baseline(entry(quoted, 'argument.type', 1, 'Model/Generator.php'))],
    ['the same message under another identifier', baseline(entry(quoted, 'return.type', 1, 'Model/Feed.php'))]
]) {
    test(`the guard rejects ${name}`, () => {
        const result = compare(reference, current);
        assert.notEqual(result.status, 0);
        assert.match(result.stderr, /may only shrink/);
    });
}

test('a missing or zero reference commit is reported as the first reviewed baseline', () => {
    const result = spawnSync('php', [guard, '--git-ref=0000000000000000000000000000000000000000', guard], {
        encoding: 'utf8'
    });
    assert.equal(result.status, 0, result.stderr);
    assert.match(result.stdout, /No reference commit/);
});
