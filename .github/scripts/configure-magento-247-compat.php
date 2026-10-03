<?php
declare(strict_types=1);

// This changes only the disposable Magento project, never the extension manifest.
try {
    $path = $argv[1] ?? '';
    $project = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    if (($project['name'] ?? '') !== 'magento/project-community-edition'
        || ($project['require']['magento/product-community-edition'] ?? '') !== '2.4.7-p10'
    ) {
        throw new RuntimeException('The exception is restricted to the Magento 2.4.7-p10 test project.');
    }

    $id = 'PKSA-w9tt-7782-78jx';
    $config = $project['config'] ?? [];
    $audit = $config['audit'] ?? [];
    $ignore = $audit['ignore'] ?? [];
    if (array_key_exists('policy', $config)
        || !is_array($audit)
        || !is_array($ignore)
        || ($audit['block-insecure'] ?? true) !== true
        || array_diff(array_keys($audit), ['ignore', 'block-insecure']) !== []
        || array_diff(array_keys($ignore), [$id]) !== []
        || (isset($ignore[$id]) && ($ignore[$id]['apply'] ?? '') !== 'block')
    ) {
        throw new RuntimeException('Refusing to combine this exception with other audit overrides.');
    }

    $project['config']['audit'] = [
        'block-insecure' => true,
        'ignore' => [
            $id => [
                'apply' => 'block',
                'reason' => 'Disposable Magento 2.4.7-p10 compatibility tests; Flysystem 2.x has no fixed release. '
                    . 'Keep the advisory in the audit and fail on every other advisory.'
            ]
        ]
    ];
    $json = json_encode($project, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
    if (file_put_contents($path, $json) === false) {
        throw new RuntimeException('Unable to write the disposable project configuration.');
    }
    echo "Configured one blocking-only exception for Magento 2.4.7-p10; audit reporting remains enabled.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
