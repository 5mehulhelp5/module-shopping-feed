<?php

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\LocalInventory\Simple;

use MageOS\ShoppingFeed\Model\Product\Mapper\LocalInventory\Simple\InventorySource;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class InventorySourceTest extends ModelFramework
{
    /**
     * @var InventorySource
     */
    private $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new InventorySource($this->loggerMock);
        $this->mapper->addAdapter($this->adapterMock);
        $this->expectReturn($this->adapterMock, 'getFeed', $this->feedMock);
    }

    public function testMapsMsiSourceCodeToGoogleStoreCode(): void
    {
        $sourceItem = $this->createMock('Magento\InventoryApi\Api\Data\SourceItemInterface');
        $this->expectReturn($sourceItem, 'getSourceCode', 'warehouse_indy');
        $this->expectReturn($this->adapterMock, 'getData', $sourceItem);
        $this->feedMock->method('getConfig')->willReturnCallback(
            static function ($key, $default = '') {
                return [
                    'general_use_default_stock' => true,
                    'general_stock_attribute_code' => '',
                    'categories_inventory_source_map' => "warehouse_indy=INDIANAPOLIS-01\nwarehouse_la=LOS-ANGELES-02",
                ][$key] ?? $default;
            }
        );

        $this->assertSame('INDIANAPOLIS-01', $this->mapper->map());
    }

    public function testKeepsUnmappedSourceCode(): void
    {
        $sourceItem = $this->createMock('Magento\InventoryApi\Api\Data\SourceItemInterface');
        $this->expectReturn($sourceItem, 'getSourceCode', 'warehouse_indy');
        $this->expectReturn($this->adapterMock, 'getData', $sourceItem);
        $this->feedMock->method('getConfig')->willReturnCallback(
            static function ($key, $default = '') {
                return [
                    'general_use_default_stock' => true,
                    'general_stock_attribute_code' => '',
                    'categories_inventory_source_map' => '',
                ][$key] ?? $default;
            }
        );

        $this->assertSame('warehouse_indy', $this->mapper->map());
    }

    public function testCanMapCustomStockFallback(): void
    {
        $this->feedMock->method('getConfig')->willReturnCallback(
            static function ($key, $default = '') {
                return [
                    'general_use_default_stock' => false,
                    'general_stock_attribute_code' => 'warehouse_stock',
                    'categories_inventory_source_map' => 'custom=MAIN-STORE',
                ][$key] ?? $default;
            }
        );

        $this->assertSame('MAIN-STORE', $this->mapper->map());
    }
}
