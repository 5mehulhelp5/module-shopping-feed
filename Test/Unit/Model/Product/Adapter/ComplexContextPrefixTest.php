<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Adapter;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Framework\ObjectManagerInterface;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ComplexContextPrefixTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $previousObjectManager = null;

    protected function setUp(): void
    {
        try {
            $this->previousObjectManager = ObjectManager::getInstance();
        } catch (\RuntimeException $missing) {
            $this->previousObjectManager = null;
        }
    }

    protected function tearDown(): void
    {
        if ($this->previousObjectManager !== null) {
            ObjectManager::setInstance($this->previousObjectManager);
        }
    }

    public function testParentLookupsUseTheStoresTablePrefix(): void
    {
        $product = (new \ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $product->setData(['entity_id' => 7, 'type_id' => 'simple', 'visibility' => 4]);
        $website = $this->createMock(\Magento\Store\Model\Website::class);
        $website->method('getId')->willReturn(1);
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->method('getWebsite')->willReturn($website);
        $feed = $this->createMock(Feed::class);
        $feed->method('getStore')->willReturn($store);
        $feed->method('isProductTypeEnabled')->willReturn(true);

        $connection = $this->createMock(Mysql::class);
        $connection->method('quoteInto')->willReturnCallback(
            static fn (string $text, $value): string => str_replace('?', "'" . $value . "'", $text)
        );
        $connection->method('select')->willReturnCallback(
            fn (): Select => new Select($connection, $this->createMock(SelectRenderer::class))
        );
        $tables = [];
        $connection->method('fetchOne')->willReturnCallback(
            static function ($query) use (&$tables) {
                if ($query instanceof Select) {
                    foreach ($query->getPart(Select::FROM) as $from) {
                        $tables[] = $from['tableName'];
                    }
                } else {
                    preg_match_all('/\b(?:FROM|JOIN)\s+([a-z_]+)/i', (string) $query, $matches);
                    array_push($tables, ...$matches[1]);
                }
                return false;
            }
        );
        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);
        $resource->method('getTableName')->willReturnCallback(static fn ($table): string => 'pfx_' . $table);
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->method('get')->willReturnMap([[ResourceConnection::class, $resource]]);
        ObjectManager::setInstance($objectManager);

        $adapter = $this->getMockBuilder(Simple::class)->disableOriginalConstructor()
            ->onlyMethods(['getProduct', 'getFeed'])->getMock();
        $adapter->method('getProduct')->willReturn($product);
        $adapter->method('getFeed')->willReturn($feed);
        (new \ReflectionProperty(Simple::class, 'eavLinkField'))->setValue($adapter, 'entity_id');

        $result = (new \ReflectionMethod(Simple::class, 'getProductInComplexProduct'))->invoke($adapter);

        $this->assertSame([], $result);
        $this->assertNotEmpty($tables);
        $this->assertSame(
            [],
            array_values(array_filter($tables, static fn ($table) => !str_starts_with($table, 'pfx_')))
        );
    }
}
