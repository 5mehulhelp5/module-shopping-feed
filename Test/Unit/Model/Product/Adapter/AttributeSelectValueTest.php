<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Adapter;

use MageOS\ShoppingFeed\Model\Generator\Cache;
use MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class AttributeSelectValueTest extends TestCase
{
    /** @dataProvider optionCases */
    #[DataProvider('optionCases')]
    public function testUsesFeedStoreLabelsAndPreservesMultiselectOrder(mixed $value, string $expected): void
    {
        $adapter = $this->adapter(2, new Cache());
        $attribute = $this->attribute();
        $product = (new \ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $product->setData('manufacturer', $value);
        self::assertSame($expected, $adapter->getAttributeValue($product, $attribute));
        self::assertSame(7, $attribute->getStoreId(), 'The shared attribute keeps its original store context.');
    }

    /**
     * @return array<string, array{mixed, string}>
     */
    public static function optionCases(): array
    {
        return [
            'select' => ['10', '品牌'],
            'multiselect string' => ['11,10', 'Fallback, 品牌'],
            'multiselect array' => [[10, 11], '品牌, Fallback'],
            'missing store label' => ['11', 'Fallback'],
            'unknown option' => ['999', ''],
            'empty selection' => [null, ''],
        ];
    }

    public function testCacheSeparatesStoresAndReusesEachStoresLabels(): void
    {
        $cache = new Cache();
        $attribute = $this->attribute(2);
        $product = (new \ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $product->setData('manufacturer', '10');
        foreach ([2 => '品牌', 1 => 'Brand'] as $storeId => $expected) {
            self::assertSame($expected, $this->adapter($storeId, $cache)->getAttributeValue($product, $attribute));
        }
        self::assertSame('品牌', $this->adapter(2, $cache)->getAttributeValue($product, $attribute));
        self::assertSame(7, $attribute->getStoreId());
    }

    public function testRestoresAttributeStoreWhenTheSourceThrows(): void
    {
        $attribute = $this->attribute(null, true);
        $product = (new \ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $product->setData('manufacturer', '10');
        try {
            $this->adapter(2, new Cache())->getAttributeValue($product, $attribute);
            self::fail('The source exception must propagate.');
        } catch (\RuntimeException $exception) {
            self::assertSame('source failure', $exception->getMessage());
        }
        self::assertSame(7, $attribute->getStoreId());
    }

    private function adapter(int $storeId, Cache $cache): AdapterAbstract
    {
        $adapter = (new \ReflectionClass(AdapterAbstract::class))->newInstanceWithoutConstructor();
        $store = $this->createMock(Store::class);
        $store->method('getId')->willReturn($storeId);
        foreach (['storeObject' => $store, 'cache' => $cache, 'jsonSerializer' => new Json()] as $name => $value) {
            (new \ReflectionProperty(AdapterAbstract::class, $name))->setValue($adapter, $value);
        }
        return $adapter;
    }

    private function attribute(?int $sourceCalls = null, bool $sourceFails = false): Attribute
    {
        $attribute = $this->getMockBuilder(Attribute::class)->disableOriginalConstructor()
            ->onlyMethods(['getOptions', 'getSource'])->getMock();
        $attribute->setData(['attribute_code' => 'manufacturer', 'frontend_input' => 'select', 'store_id' => 7]);
        $source = $this->createMock(Table::class);
        if ($sourceFails) {
            $source->method('getAllOptions')->willThrowException(new \RuntimeException('source failure'));
        } else {
            $source->expects($sourceCalls === null ? $this->any() : $this->exactly($sourceCalls))
                ->method('getAllOptions')->willReturnCallback(static function () use ($attribute): array {
                    return [
                        ['value' => '', 'label' => 'Please select'],
                        ['value' => '10', 'label' => (int)$attribute->getStoreId() === 2 ? '品牌' : 'Brand'],
                        ['value' => '11', 'label' => 'Fallback'],
                    ];
                });
        }
        $attribute->method('getSource')->willReturn($source);
        $options = [];
        foreach (['10' => 'Brand', '11' => 'Fallback'] as $value => $label) {
            $option = (new \ReflectionClass(Option::class))->newInstanceWithoutConstructor();
            $options[] = $option->setData(['value' => $value, 'label' => $label]);
        }
        $attribute->method('getOptions')->willReturn($options);
        return $attribute;
    }
}
