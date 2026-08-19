<?php
declare(strict_types=1);

// A validation checkout can contain dependencies that emit deprecations on a
// newer PHP runtime. Report module schema failures without that unrelated noise.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

$moduleRoot = dirname(__DIR__, 2);
$magentoRoot = $argv[1] ?? getenv('MAGENTO_ROOT') ?: '';
if ($magentoRoot === '' || !is_file($magentoRoot . '/app/autoload.php')) {
    fwrite(STDERR, "Usage: php dev/tests/validate-magento-xml.php /path/to/magento\n");
    exit(2);
}

require $magentoRoot . '/app/autoload.php';

$componentRegistrar = new Magento\Framework\Component\ComponentRegistrar();
if ($componentRegistrar->getPath(
    Magento\Framework\Component\ComponentRegistrar::MODULE,
    'MageOS_ShoppingFeed'
) === null) {
    require $moduleRoot . '/registration.php';
}

$failures = [];
$validated = 0;
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($moduleRoot, FilesystemIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'xml') {
        continue;
    }
    if (str_contains($file->getPathname(), DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR)) {
        continue;
    }

    $document = new DOMDocument();
    if (!$document->load($file->getPathname(), LIBXML_NONET)) {
        $failures[] = $file->getPathname() . ': not well-formed';
        continue;
    }

    $schema = $document->documentElement?->getAttributeNS(
        'http://www.w3.org/2001/XMLSchema-instance',
        'noNamespaceSchemaLocation'
    );
    if ($schema === '') {
        $failures[] = $file->getPathname() . ': missing schema declaration';
        continue;
    }

    try {
        $errors = Magento\Framework\Config\Dom::validateDomDocument($document, $schema);
    } catch (Throwable $exception) {
        $failures[] = $file->getPathname() . ': ' . $exception->getMessage();
        continue;
    }
    foreach ($errors as $error) {
        $failures[] = $file->getPathname() . ': ' . (string) $error;
    }
    $validated++;
}

if ($failures !== []) {
    fwrite(STDERR, "Magento XML validation failed:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

printf("Magento XML validation passed for %d files.\n", $validated);
