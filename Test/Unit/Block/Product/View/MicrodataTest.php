<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Block\Product\View;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use MageOS\ShoppingFeed\Block\Product\View\Microdata;
use MageOS\ShoppingFeed\Model\MicrodataFactory;
use MageOS\ShoppingFeed\Test\Unit\CompatibilityTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class MicrodataTest extends CompatibilityTestCase
{
    /** @dataProvider associatedProductProvider */
    #[DataProvider('associatedProductProvider')]
    public function testOnlyEligibleConfigurableChildrenSupplyMicrodata($aid, string $type, bool $related, int $status, array $websites, bool $accepted, int $childId = 20): void
    {
        $parent = $this->createCompatibleMock(Product::class, ['getTypeId', 'getId', 'getTypeInstance']);
        $parent->method('getId')->willReturn(10);
        $parent->method('getTypeId')->willReturn($type);
        $productType = $this->createMock(Configurable::class);
        $productType->method('getChildrenIds')->with(10)->willReturn([0 => $related ? [20 => 20] : [30 => 30]]);
        $parent->method('getTypeInstance')->willReturn($productType);

        $child = $this->createCompatibleMock(Product::class, ['load', 'getId', 'getStatus', 'getWebsiteIds', 'setStoreId']);
        $child->method('setStoreId')->with(2)->willReturnSelf();
        $child->method('load')->willReturnSelf();
        $child->method('getId')->willReturn($childId);
        $child->method('getStatus')->willReturn($status);
        $child->method('getWebsiteIds')->willReturn($websites);
        $products = $this->createMock(ProductFactory::class);
        $products->method('create')->willReturn($child);

        $store = $this->createCompatibleMock(Store::class, ['getId', 'getWebsiteId']);
        $store->method('getId')->willReturn(2);
        $store->method('getWebsiteId')->willReturn(5);
        $stores = $this->createMock(StoreManagerInterface::class);
        $stores->method('getStore')->willReturn($store);
        $request = $this->createMock(Http::class);
        $request->method('getParam')->with('aid', false)->willReturn($aid);
        $request->method('getParams')->willReturn(['aid' => $aid]);
        $factory = $this->createMock(MicrodataFactory::class);
        $factory->expects($this->once())->method('create')->with($this->callback(
            fn(array $data): bool => $data['product'] === ($accepted ? $child : $parent)
                && $data['assoc_id'] === ($accepted ? 20 : false)
                && $data['block_product'] === $parent
        ))->willReturn($this->createMock(\MageOS\ShoppingFeed\Model\Microdata::class));

        $block = $this->getMockBuilder(Microdata::class)->disableOriginalConstructor()
            ->onlyMethods(['getProduct', 'getRequest'])->getMock();
        $block->method('getProduct')->willReturn($parent);
        $block->method('getRequest')->willReturn($request);
        foreach (['_storeManager' => $stores, 'productFactory' => $products, 'microdataFactory' => $factory] as $property => $value) {
            (new \ReflectionProperty(Microdata::class, $property))->setValue($block, $value);
        }
        (new \ReflectionMethod(Microdata::class, 'getModel'))->invoke($block);
    }

    public static function associatedProductProvider(): array
    {
        return [
            'valid child' => ['20', 'configurable', true, 1, ['5'], true],
            'deleted child' => ['20', 'configurable', true, 1, [5], false, 0],
            'disabled child' => ['20', 'configurable', true, 2, [5], false],
            'other website' => ['20', 'configurable', true, 1, [1], false],
            'unrelated product' => ['20', 'configurable', false, 1, [5], false],
            'simple page' => ['20', 'simple', true, 1, [5], false],
            'missing aid' => [false, 'configurable', true, 1, [5], false],
            'malformed aid' => ['20abc', 'configurable', true, 1, [5], false],
            'array aid' => [[20], 'configurable', true, 1, [5], false],
            'zero aid' => ['0', 'configurable', true, 1, [5], false],
        ];
    }
}
