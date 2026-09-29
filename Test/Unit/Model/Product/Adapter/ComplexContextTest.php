<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Adapter;

use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ComplexContextTest extends TestCase
{
    /** @dataProvider visibilityCases */
    #[DataProvider('visibilityCases')]
    public function testEveryIndividuallyVisibleChildUsesItsEnabledParent(int $visibility, array $expected): void
    {
        $product = (new \ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $product->setData(['entity_id' => 1, 'type_id' => 'simple', 'visibility' => $visibility]);
        $website = $this->createMock(\Magento\Store\Model\Website::class);
        $website->method('getId')->willReturn(1);
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->method('getWebsite')->willReturn($website);
        $feed = $this->createMock(Feed::class);
        $feed->method('getStore')->willReturn($store);
        $feed->method('isProductTypeEnabled')->willReturnCallback(static fn($type) => $type === 'configurable');
        $connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $connection->expects($expected ? $this->once() : $this->never())->method('fetchOne')->willReturn(99);
        $adapter = $this->getMockBuilder(Simple::class)->disableOriginalConstructor()
            ->onlyMethods(['getProduct', 'getFeed', 'getDbConnection'])->getMock();
        $adapter->method('getProduct')->willReturn($product);
        $adapter->method('getFeed')->willReturn($feed);
        $adapter->method('getDbConnection')->willReturn($connection);
        (new \ReflectionProperty(Simple::class, 'eavLinkField'))->setValue($adapter, 'entity_id');
        $result = (new \ReflectionMethod(Simple::class, 'getProductInComplexProduct'))->invoke($adapter);
        $this->assertSame($expected, $result);
    }

    public static function visibilityCases(): array
    {
        return [
            'not individually visible' => [1, []],
            'catalog' => [2, [99 => 'configurable']],
            'search' => [3, [99 => 'configurable']],
            'catalog and search' => [4, [99 => 'configurable']],
        ];
    }
}
