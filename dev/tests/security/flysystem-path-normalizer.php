<?php
declare(strict_types=1);

// Run against the installed library or an isolated patched copy of its src directory.
$root = rtrim($argv[1] ?? '', '/');
$source = rtrim($argv[2] ?? $root . '/vendor/league/flysystem/src', '/');
require $root . '/vendor/autoload.php';
require $source . '/WhitespacePathNormalizer.php';

$normalizer = new \League\Flysystem\WhitespacePathNormalizer();
$failures = [];
$cases = 0;
foreach ([
    ['catalog/image.jpg', 'catalog/image.jpg'],
    ['catalog\\image.jpg', 'catalog/image.jpg'],
    ['catalog/./image.jpg', 'catalog/image.jpg'],
    ['catalog/old/../image.jpg', 'catalog/image.jpg'],
    ['café/商品.jpg', 'café/商品.jpg'],
    ['a space/image.jpg', 'a space/image.jpg'],
] as [$path, $expected]) {
    $cases++;
    if ($normalizer->normalizePath($path) !== $expected) {
        $failures[] = 'Valid path normalization changed: case ' . $cases;
    }
}
foreach (["some\0/path.txt", "s\x09 i.php", "foo\x80\x1b bar", "foo\x80bar", "foo\xc0\xafbar"] as $path) {
    $cases++;
    try {
        $normalizer->normalizePath($path);
        $failures[] = 'Corrupted path was accepted: case ' . $cases;
    } catch (\League\Flysystem\CorruptedPathDetected $expected) {
        // Do not print potentially unsafe filename bytes to the terminal.
    }
}
$cases++;
try {
    $normalizer->normalizePath('../outside.txt');
    $failures[] = 'Path traversal was accepted.';
} catch (\League\Flysystem\PathTraversalDetected $expected) {
}
if ($failures) {
    fwrite(STDERR, implode("\n", $failures) . "\n");
    exit(1);
}
echo $cases . " Flysystem path-normalizer checks passed.\n";
