<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Adapter;

use MageOS\ShoppingFeed\Model\Generator\Cache;
use MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
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
        $product = $this->product($value);
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
        $product = $this->product('10');
        foreach ([2 => '品牌', 1 => 'Brand'] as $storeId => $expected) {
            self::assertSame($expected, $this->adapter($storeId, $cache)->getAttributeValue($product, $attribute));
        }
        self::assertSame('品牌', $this->adapter(2, $cache)->getAttributeValue($product, $attribute));
        self::assertSame(7, $attribute->getStoreId());
    }

    public function testRestoresAttributeStoreWhenTheSourceThrows(): void
    {
        $attribute = $this->attribute(null, true);
        $product = $this->product('10');
        try {
            $this->adapter(2, new Cache())->getAttributeValue($product, $attribute);
            self::fail('The source exception must propagate.');
        } catch (\RuntimeException $exception) {
            self::assertSame('source failure', $exception->getMessage());
        }
        self::assertSame(7, $attribute->getStoreId());
    }

    /**
     * Custom sources may omit the value, use null, or nest option groups. Magento's error handler turns
     * every PHP warning and deprecation into an exception, so those rows must be skipped, not read.
     */
    public function testSkipsSourceOptionsWithoutUsableValuesUnderMagentoErrorHandling(): void
    {
        $attribute = $this->attribute(null, false, [
            ['label' => 'No value key'],
            ['value' => null, 'label' => 'Null value'],
            ['value' => ['nested' => 'Child'], 'label' => 'Option group'],
            ['value' => '', 'label' => 'Please select'],
            ['value' => '10', 'label' => 'Brand'],
            ['value' => 0, 'label' => 'Zero'],
        ]);
        $product = $this->product('10,0,');
        $reporting = error_reporting(E_ALL);
        set_error_handler(static function (int $number, string $message): bool {
            throw new \ErrorException($message, 0, $number);
        });
        try {
            $value = $this->adapter(2, new Cache())->getAttributeValue($product, $attribute);
        } finally {
            restore_error_handler();
            error_reporting($reporting);
        }
        self::assertSame('Brand, Zero', $value);
    }

    private function product(mixed $manufacturer): Product
    {
        $product = (new \ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $product->setData('manufacturer', $manufacturer);
        return $product;
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

    /**
     * @param int|null $sourceCalls Exact number of expected source reads, or null for any
     * @param bool $sourceFails Whether the source throws instead of returning options
     * @param array<int, array<string, mixed>>|null $rows Fixed source rows, or null for store-dependent labels
     */
    private function attribute(?int $sourceCalls = null, bool $sourceFails = false, ?array $rows = null): Attribute
    {
        $attribute = $this->getMockBuilder(Attribute::class)->disableOriginalConstructor()
            ->onlyMethods(['getSource'])->getMock();
        $attribute->setData(['attribute_code' => 'manufacturer', 'frontend_input' => 'select', 'store_id' => 7]);
        $source = $this->createMock(Table::class);
        $expectation = $source->expects($sourceCalls === null ? $this->any() : $this->exactly($sourceCalls))
            ->method('getAllOptions');
        if ($sourceFails) {
            $expectation->willThrowException(new \RuntimeException('source failure'));
        } elseif ($rows !== null) {
            $expectation->willReturn($rows);
        } else {
            $expectation->willReturnCallback(static function () use ($attribute): array {
                return [
                    ['value' => '', 'label' => 'Please select'],
                    ['value' => '10', 'label' => (int)$attribute->getStoreId() === 2 ? '品牌' : 'Brand'],
                    ['value' => '11', 'label' => 'Fallback'],
                ];
            });
        }
        $attribute->method('getSource')->willReturn($source);
        return $attribute;
    }
}
