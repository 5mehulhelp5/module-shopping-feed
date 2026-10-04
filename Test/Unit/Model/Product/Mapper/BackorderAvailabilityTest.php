<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper;

use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Inventory\Api;
use MageOS\ShoppingFeed\Model\Logger;
use MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple as Adapter;
use MageOS\ShoppingFeed\Model\Product\Filter;
use MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Availability;
use MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\Availability as ConfigurableAvailability;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\CatalogInventory\Model\StockRegistryProvider;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class BackorderAvailabilityTest extends TestCase
{
    /** @dataProvider backorderCases */
    #[DataProvider('backorderCases')]
    public function testEffectiveBackorderSetting(int $raw, int $effective, float $qty, string $expected): void
    {
        $stock = $this->getMockBuilder(Item::class)->disableOriginalConstructor()
            ->onlyMethods(['getManageStock', 'getStoreId'])->getMock();
        $stock->setData(['item_id' => 1, 'backorders' => $raw, 'use_config_backorders' => true]);
        $config = $this->createMock(StockConfigurationInterface::class);
        $config->method('getBackorders')->willReturn($effective);
        (new \ReflectionProperty(Item::class, 'stockConfiguration'))->setValue($stock, $config);
        $stock->method('getManageStock')->willReturn(true);
        $stock->method('getStoreId')->willReturn(1);
        $provider = $this->createMock(StockRegistryProvider::class);
        $provider->method('getStockItem')->willReturn($stock);
        $status = $this->getMockBuilder(Status::class)->disableOriginalConstructor()->onlyMethods(['load'])->getMock();
        $status->method('load')->willReturnSelf();
        $status->setData(['stock_status' => 1, 'qty' => $qty]);
        $inventory = $this->createMock(Api::class);
        $inventory->method('getAllItems')->willReturn([]);
        $mapper = new Availability($this->createMock(Logger::class), $status, $inventory, $provider);
        $adapter = $this->adapter();
        $mapper->addAdapter($adapter);
        $this->assertSame($expected, $mapper->getStockStatus($adapter));
    }

    public static function backorderCases(): array
    {
        return [
            'inherited enabled' => [0, 1, 0, Availability::BACKORDER],
            'inherited disabled' => [1, 0, 0, Availability::OUT_OF_STOCK],
            'positive quantity' => [0, 1, 5, Availability::IN_STOCK],
        ];
    }

    /** @dataProvider configurableCases */
    #[DataProvider('configurableCases')]
    public function testConfigurableAggregatesSalableChildren(string $parent, array $children, string $expected): void
    {
        $adapter = $this->adapter();
        $childAdapters = array_map(fn() => $this->adapter(), $children);
        $adapter->setData('associated_product_adapters', $childAdapters);
        $mapper = $this->getMockBuilder(ConfigurableAvailability::class)->disableOriginalConstructor()
            ->onlyMethods(['getStockStatus'])->getMock();
        $mapper->method('getStockStatus')->willReturnOnConsecutiveCalls($parent, ...$children);
        $mapper->addAdapter($adapter);
        $this->assertSame($expected, $mapper->map());
    }

    public static function configurableCases(): array
    {
        return [
            'only backorders' => ['in_stock', ['backorder'], 'backorder'],
            'only preorders' => ['in_stock', ['preorder'], 'preorder'],
            'in stock wins' => ['backorder', ['backorder', 'in_stock'], 'in_stock'],
            'backorder before preorder' => ['in_stock', ['backorder', 'preorder'], 'backorder'],
            'backorder after preorder' => ['in_stock', ['preorder', 'backorder'], 'backorder'],
            'no salable child' => ['in_stock', ['out_of_stock'], 'out_of_stock'],
            'parent out of stock' => ['out_of_stock', ['in_stock'], 'out_of_stock'],
            'no children' => ['backorder', [], 'backorder'],
        ];
    }

    public function testInStockMsiSourceWinsOverLaterBackorderedSource(): void
    {
        $adapter = $this->adapter();
        $stock = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $stock->setData('backorders', 1);
        $provider = $this->createMock(StockRegistryProvider::class);
        $provider->method('getStockItem')->willReturn($stock);
        $items = [];
        foreach ([5.0, 0.0] as $quantity) {
            $item = $this->createMock(\Magento\InventoryApi\Api\Data\SourceItemInterface::class);
            $item->method('getStatus')->willReturn(1);
            $item->method('getQuantity')->willReturn($quantity);
            $items[] = $item;
        }
        $inventory = $this->createMock(Api::class);
        $inventory->method('getAllItems')->willReturn($items);
        $inventory->method('getItems')->willReturn($items);
        $mapper = new Availability(
            $this->createMock(Logger::class),
            $this->createMock(Status::class),
            $inventory,
            $provider
        );
        $mapper->addAdapter($adapter);
        $this->assertSame(Availability::IN_STOCK, $mapper->getStockStatus($adapter));
    }

    private function adapter(): Adapter
    {
        $feed = $this->createMock(Feed::class);
        $feed->method('getConfig')->willReturnCallback(fn($key) => $key === 'general_use_default_stock');
        $website = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()->onlyMethods([])->getMock();
        $website->setData('code', 'base');
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->method('getWebsite')->willReturn($website);
        $feed->method('getStore')->willReturn($store);
        $filter = $this->createMock(Filter::class);
        $filter->method('cleanField')->willReturnArgument(0);
        $adapter = $this->getMockBuilder(Adapter::class)->disableOriginalConstructor()
            ->onlyMethods(['getProduct', 'getFeed', 'getFilter'])->getMock();
        $product = $this->getMockBuilder(Product::class)->disableOriginalConstructor()
            ->onlyMethods(['getStoreId'])->getMock();
        $product->method('getStoreId')->willReturn(1);
        $adapter->method('getProduct')->willReturn($product);
        $adapter->method('getFeed')->willReturn($feed);
        $adapter->method('getFilter')->willReturn($filter);
        return $adapter;
    }
}
