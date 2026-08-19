<?php
declare(strict_types=1);

$magentoRoot = getenv('MAGENTO_ROOT') ?: '';
$magentoBootstrap = $magentoRoot . '/dev/tests/unit/framework/bootstrap.php';
if ($magentoRoot === '' || !is_file($magentoBootstrap)) {
    throw new RuntimeException(
        'Set MAGENTO_ROOT to a Magento or Mage-OS checkout before running the unit suite.'
    );
}

$testTempDirectory = sys_get_temp_dir() . '/mageos-shopping-feed-unit-tests';
if (!is_dir($testTempDirectory) && !mkdir($testTempDirectory, 0777, true) && !is_dir($testTempDirectory)) {
    throw new RuntimeException('Unable to create the unit-test temporary directory.');
}
define('TESTS_TEMP_DIR', $testTempDirectory);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require $magentoBootstrap;

// A validation checkout may emit dependency deprecations on a newer PHP
// runtime. Keep those from being promoted to test errors by Magento's handler.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

$moduleRoot = dirname(__DIR__, 2);
spl_autoload_register(static function (string $class) use ($moduleRoot): void {
    $prefix = 'MageOS\\ShoppingFeed\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix))) . '.php';
    $path = $moduleRoot . DIRECTORY_SEPARATOR . $relativePath;
    if (is_file($path)) {
        require $path;
    }
}, true, true);

require_once __DIR__ . '/Model/_files/global_mock_functions.php';
