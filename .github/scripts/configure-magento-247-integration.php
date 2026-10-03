<?php
declare(strict_types=1);

$root = rtrim($argv[1] ?? '', '/');
$project = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
if (($project['require']['magento/product-community-edition'] ?? '') !== '2.4.7-p10') {
    throw new RuntimeException('Expected the disposable Magento 2.4.7-p10 project.');
}
$directory = $root . '/dev/tests/integration';
// Keep framework constants in the generated PHP. They are available when the
// integration bootstrap loads it, but not while this standalone helper runs.
$config = <<<'PHP'
<?php
return [
    'db-host' => '127.0.0.1',
    'db-user' => 'user',
    'db-password' => 'password',
    'db-name' => 'magento_integration_tests',
    'db-prefix' => '',
    'backend-frontname' => 'backend',
    'search-engine' => 'opensearch',
    'opensearch-host' => '127.0.0.1',
    'opensearch-port' => 9200,
    'admin-user' => \Magento\TestFramework\Bootstrap::ADMIN_NAME,
    'admin-password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
    'admin-email' => \Magento\TestFramework\Bootstrap::ADMIN_EMAIL,
    'admin-firstname' => \Magento\TestFramework\Bootstrap::ADMIN_FIRSTNAME,
    'admin-lastname' => \Magento\TestFramework\Bootstrap::ADMIN_LASTNAME,
    'consumers-wait-for-messages' => '0',
];
PHP;
file_put_contents($directory . '/etc/install-config-mysql.php', $config . "\n");
$xml = simplexml_load_file($directory . '/phpunit.xml.dist');
unset($xml->testsuites);
$suite = $xml->addChild('testsuites')->addChild('testsuite');
$suite->addAttribute('name', 'ShoppingFeed');
$suite->addChild('directory', '../../../vendor/mage-os/module-shopping-feed/Test/Integration');
$xml->asXML($directory . '/phpunit.xml');
echo "Configured the dedicated integration database and Shopping Feed suite.\n";
