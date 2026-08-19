<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Processors;

use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode;
use MageOS\ShoppingFeed\Model\Product\Adapter\Type\Configurable;
use MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple;
use MageOS\ShoppingFeed\Model\Product\Processors\LocalInventory;
use MageOS\ShoppingFeed\Test\Unit\CompatibilityTestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class LocalInventoryTest extends CompatibilityTestCase
{
    public function testRemapsSourcesWithoutTestModeOrLosingAssociatedAdapters(): void
    {
        $sourceItem = new class extends DataObject {
            public function getSourceItemId(): int
            {
                return 7;
            }
        };

        $parentProduct = $this->createMock(Product::class);
        $associatedProduct = $this->createMock(Product::class);
        $associatedAdapter = $this->createCompatibleMock(
            Simple::class,
            ['getFeed', 'getProduct', 'setData', 'setTestMode']
        );
        $adapter = $this->createCompatibleMock(
            Configurable::class,
            [
                'getFeed', 'getProduct', 'getData', 'setData', 'getAssociatedProductsMode',
                'internalMap', 'mapAssociatedProducts', 'setTestMode'
            ]
        );

        $feed = $this->createCompatibleMock(Feed::class, ['getStore', 'getColumnsMap', 'setColumnsMap']);
        $website = $this->createMock(Website::class);
        $website->method('getCode')->willReturn('base');
        $store = $this->createMock(Store::class);
        $store->method('getWebsite')->willReturn($website);
        $feed->method('getStore')->willReturn($store);
        $feed->method('getColumnsMap')->willReturn([]);
        $feed->method('setColumnsMap')->willReturnSelf();

        $adapterData = ['associated_product_adapters' => [$associatedAdapter]];
        $adapter->method('getFeed')->willReturn($feed);
        $adapter->method('getProduct')->willReturn($parentProduct);
        $adapter->method('getAssociatedProductsMode')->willReturn(AssociatedMode::BOTH_PARENT_ASSOCIATED);
        $adapter->method('getData')->willReturnCallback(
            static fn (string $key) => $adapterData[$key] ?? null
        );
        $adapter->method('setData')->willReturnCallback(
            static function (string $key, $value) use (&$adapterData, $adapter) {
                $adapterData[$key] = $value;
                return $adapter;
            }
        );
        $adapter->expects($this->once())
            ->method('internalMap')
            ->with(false, false)
            ->willReturnCallback(
                function () use (&$adapterData, $associatedAdapter): array {
                    $this->assertSame(
                        [$associatedAdapter],
                        $adapterData['associated_product_adapters']
                    );
                    return [['parent']];
                }
            );
        $adapter->expects($this->once())->method('mapAssociatedProducts')->with(false)->willReturn([['associated']]);
        $adapter->expects($this->never())->method('setTestMode');

        $associatedAdapter->method('getFeed')->willReturn($feed);
        $associatedAdapter->method('getProduct')->willReturn($associatedProduct);
        $associatedAdapter->method('setData')->willReturnSelf();
        $associatedAdapter->expects($this->never())->method('setTestMode');

        $criteria = $this->createMock(SearchCriteriaInterface::class);
        $searchCriteriaBuilder = $this->createCompatibleMock(
            SearchCriteriaBuilder::class,
            ['addFilter', 'create']
        );
        $searchCriteriaBuilder->method('addFilter')->willReturnSelf();
        $searchCriteriaBuilder->method('create')->willReturn($criteria);

        $stockResolver = new class {
            public function execute(): object
            {
                return new class {
                    public function getStockId(): int
                    {
                        return 1;
                    }
                };
            }
        };
        $stockLinks = new class {
            public function execute(): object
            {
                return new class {
                    public function getItems(): array
                    {
                        return [new class {
                            public function getSourceCode(): string
                            {
                                return 'default';
                            }
                        }];
                    }
                };
            }
        };
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->method('create')->willReturnOnConsecutiveCalls($stockResolver, $stockLinks);

        $processor = $this->getMockBuilder(LocalInventory::class)
            ->setConstructorArgs([$objectManager, $searchCriteriaBuilder, $this->createMock(Manager::class)])
            ->onlyMethods(['usesDefaultStock', 'isMsiEnabled', 'getSourceItems'])
            ->getMock();
        $processor->method('usesDefaultStock')->willReturn(true);
        $processor->method('isMsiEnabled')->willReturn(true);
        $processor->expects($this->exactly(2))
            ->method('getSourceItems')
            ->willReturn([$sourceItem]);
        $processor->setAdapter($adapter);

        $this->assertSame([['parent'], ['associated']], $processor->execute([['original']]));
        $this->assertSame([$associatedAdapter], $adapterData['associated_product_adapters']);
    }
}
