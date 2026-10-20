<?php
declare(strict_types=1);

// Reuse the existing legacy-platform policy only for its exact PHP 8.1 tool graph.
try {
    $reportPath = $argv[1] ?? '';
    $lock = json_decode((string) file_get_contents($argv[2] ?? ''), true, 512, JSON_THROW_ON_ERROR);
    $profile = $argv[3] ?? '';
    if (!is_array($lock) || !is_array($lock['packages'] ?? null)
        || !is_array($lock['packages-dev'] ?? []) || !in_array($profile, ['8.1', '8.5'], true)
    ) {
        throw new RuntimeException('Invalid dependency lock or development-audit profile.');
    }

    $versions = [];
    foreach (array_merge($lock['packages'], $lock['packages-dev'] ?? []) as $package) {
        if (!is_array($package) || !is_string($package['name'] ?? null)
            || !is_string($package['version'] ?? null) || isset($versions[$package['name']])
        ) {
            throw new RuntimeException('Invalid or duplicate package in the dependency lock.');
        }
        $versions[$package['name']] = $package['version'];
    }

    $legacy = $profile === '8.1'
        && ($versions['magento/magento2-base'] ?? '') === '2.4.7-p10'
        && ($versions['magento/framework'] ?? '') === '103.0.7-p10'
        && ($versions['league/flysystem'] ?? '') === '2.5.0';
    if ($legacy) {
        $report = json_decode((string) file_get_contents($reportPath), true, 512, JSON_THROW_ON_ERROR);
        foreach (['ignored-advisories', 'malware', 'filter', 'unreachable-repositories'] as $key) {
            if (!empty($report[$key])) {
                throw new RuntimeException('Non-empty ' . $key . ' section blocks compatibility sign-off.');
            }
        }
        echo "PHP 8.1 tools resolve the exact Magento 2.4.7-p10 compatibility dependency set.\n";
        require __DIR__ . '/check-magento-247-audit.php';
    } else {
        $argv[2] = 'extension and tools on PHP ' . $profile;
        require __DIR__ . '/check-audit-report.php';
    }
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
