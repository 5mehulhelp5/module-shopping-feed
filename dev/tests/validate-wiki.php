<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$wikiDirectory = $root . '/docs/wiki';
$failures = [];

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        $failures[] = $message;
    }
};

$assert(is_dir($wikiDirectory), 'docs/wiki directory is missing');
if (!is_dir($wikiDirectory)) {
    fwrite(STDERR, "Wiki validation failed:\n- docs/wiki directory is missing\n");
    exit(1);
}

$files = glob($wikiDirectory . '/*.md') ?: [];
sort($files);
$pageFiles = [];
$contentsByFile = [];

foreach ($files as $file) {
    $basename = basename($file);
    $contents = (string) file_get_contents($file);
    $contentsByFile[$basename] = $contents;

    $assert(
        preg_match('/^(?:_[A-Za-z]+|[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*)\.md$/', $basename) === 1,
        sprintf('Wiki filename is not GitHub Wiki safe: %s', $basename)
    );
    $assert(!str_contains($contents, '—'), sprintf('%s contains an em dash', $basename));

    if (!str_starts_with($basename, '_')) {
        $pageFiles[$basename] = true;
        $assert(
            preg_match('/^#\s+\S.+$/m', $contents) === 1,
            sprintf('%s is missing a level-one title', $basename)
        );
        $assert(
            preg_match('/> Documentation baseline: .+ Last reviewed: \d{4}-\d{2}-\d{2}\./', $contents) === 1,
            sprintf('%s is missing its documentation baseline or review date', $basename)
        );
    }
}

foreach (['Home.md', '_Sidebar.md', '_Footer.md'] as $requiredFile) {
    $assert(isset($contentsByFile[$requiredFile]), sprintf('%s is missing', $requiredFile));
}

$resolveTarget = static function (string $target): ?string {
    $target = urldecode(trim($target));
    if ($target === ''
        || str_starts_with($target, '#')
        || preg_match('/^[a-z][a-z0-9+.-]*:/i', $target) === 1
    ) {
        return null;
    }

    $target = explode('#', $target, 2)[0];
    $target = preg_replace('/\.md$/i', '', $target) ?? $target;
    return basename($target) . '.md';
};

foreach ($contentsByFile as $basename => $contents) {
    preg_match_all('/\[[^\]]+\]\(([^)]+)\)/', $contents, $matches);
    foreach ($matches[1] as $target) {
        $resolved = $resolveTarget($target);
        if ($resolved === null) {
            continue;
        }
        $assert(
            isset($contentsByFile[$resolved]),
            sprintf('%s links to missing wiki page %s', $basename, $resolved)
        );
    }
}

$sidebarTargets = [];
if (isset($contentsByFile['_Sidebar.md'])) {
    preg_match_all('/\[[^\]]+\]\(([^)]+)\)/', $contentsByFile['_Sidebar.md'], $matches);
    foreach ($matches[1] as $target) {
        $resolved = $resolveTarget($target);
        if ($resolved === null) {
            continue;
        }
        $assert(
            !isset($sidebarTargets[$resolved]),
            sprintf('_Sidebar.md links to %s more than once', $resolved)
        );
        $sidebarTargets[$resolved] = true;
    }
}

foreach (array_keys($pageFiles) as $pageFile) {
    $assert(isset($sidebarTargets[$pageFile]), sprintf('%s is missing from _Sidebar.md', $pageFile));
}
foreach (array_keys($sidebarTargets) as $sidebarTarget) {
    $assert(isset($pageFiles[$sidebarTarget]), sprintf('_Sidebar.md target is not a user-facing page: %s', $sidebarTarget));
}

$legacyAllowlist = [
    'Installation-and-Upgrade.md' => true,
    'Migration-and-Coexistence.md' => true,
];
$forbiddenLegacyInstructions = [
    'rocketweb/module-google-shopping',
    'rocket' . 'shoppingfeed:',
    'pub/media/feeds',
    'PHP 5.6',
    'Magento 2.0',
    'Products > Rocket Shopping Feeds',
    'qa-m2feed.rocketweb.com',
];
foreach ($contentsByFile as $basename => $contents) {
    if (isset($legacyAllowlist[$basename])) {
        continue;
    }
    foreach ($forbiddenLegacyInstructions as $legacyInstruction) {
        $assert(
            !str_contains($contents, $legacyInstruction),
            sprintf('%s contains legacy instruction %s', $basename, $legacyInstruction)
        );
    }
}

$maintenancePath = $root . '/docs/WIKI-MAINTENANCE.md';
$assert(is_file($maintenancePath), 'docs/WIKI-MAINTENANCE.md is missing');
if (is_file($maintenancePath)) {
    $maintenanceContents = (string) file_get_contents($maintenancePath);
    $assert(!str_contains($maintenanceContents, '—'), 'docs/WIKI-MAINTENANCE.md contains an em dash');
    $assert(
        str_contains($maintenanceContents, 'php dev/tests/validate-wiki.php'),
        'Wiki maintenance guide does not name the validation command'
    );
}

$contentMapPath = $root . '/docs/WIKI-CONTENT-MAP.md';
$assert(is_file($contentMapPath), 'docs/WIKI-CONTENT-MAP.md is missing');
if (is_file($contentMapPath)) {
    $contentMapContents = (string) file_get_contents($contentMapPath);
    $assert(!str_contains($contentMapContents, '—'), 'docs/WIKI-CONTENT-MAP.md contains an em dash');
    $assert(
        str_contains($contentMapContents, 'Google-Ads-View-Item-Events'),
        'Wiki content map does not disposition the legacy remarketing documentation'
    );
}

if ($failures !== []) {
    fwrite(STDERR, "Wiki validation failed:\n- " . implode("\n- ", array_unique($failures)) . "\n");
    exit(1);
}

printf(
    "Wiki validation passed: %d user-facing pages and %d sidebar targets.\n",
    count($pageFiles),
    count($sidebarTargets)
);
