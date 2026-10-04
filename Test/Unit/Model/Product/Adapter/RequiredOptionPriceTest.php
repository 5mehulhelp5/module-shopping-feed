<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Adapter;

use MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple;
use MageOS\ShoppingFeed\Model\Product\Helper\Catalog;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Catalog\Pricing\Price\CustomOptionPriceCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class RequiredOptionPriceTest extends TestCase
{
    /** @dataProvider optionCases */
    #[DataProvider('optionCases')]
    public function testRequiredOptionPriceDoesNotDependOnValueOrder(
        array $values,
        float $expected,
        bool $required = true
    ): void {
        $options = [];
        foreach ($values as [$raw, $converted, $default]) {
            $calculator = $this->createMock(CustomOptionPriceCalculator::class);
            $calculator->method('getOptionPriceByPriceCode')->willReturn($converted);
            $value = $this->getMockBuilder(Value::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
            (new \ReflectionProperty(Value::class, 'customOptionPriceCalculator'))->setValue($value, $calculator);
            $value->setData(['price' => $raw, 'is_default' => $default]);
            $options[] = $value;
        }
        $option = $this->getMockBuilder(Option::class)->disableOriginalConstructor()
            ->onlyMethods(['getValues'])->getMock();
        $option->method('getValues')->willReturn($options);
        $option->setData('is_require', $required);
        $parent = $this->getMockBuilder(Product::class)->disableOriginalConstructor()
            ->onlyMethods(['getOptions'])->getMock();
        $parent->method('getOptions')->willReturn([$option]);
        $parentAdapter = $this->createMock(Simple::class);
        $parentAdapter->method('getProduct')->willReturn($parent);
        $product = $this->getMockBuilder(Product::class)->disableOriginalConstructor()
            ->onlyMethods(['getPrice', 'getFinalPrice', 'getStore'])->getMock();
        $product->setData('price', 1099.99);
        $product->method('getPrice')->willReturnCallback(fn() => $product->getData('price'));
        $product->method('getFinalPrice')->willReturn(1099.99);
        $product->method('getStore')->willReturn($this->createMock(\Magento\Store\Model\Store::class));
        $adapter = $this->getMockBuilder(Simple::class)->disableOriginalConstructor()
            ->onlyMethods(['convertPrice', 'getPriceByCatalogRules', 'getSingleUnitTierPrice'])->getMock();
        $adapter->setData('parent_adapter', $parentAdapter);
        $adapter->method('convertPrice')->willReturnArgument(0);
        $adapter->method('getPriceByCatalogRules')->willReturn(false);
        $adapter->method('getSingleUnitTierPrice')->willReturn(null);
        $tax = $this->createMock(Catalog::class);
        $tax->method('getTaxPrice')->willReturnArgument(1);
        (new \ReflectionProperty(Simple::class, 'catalogHelper'))->setValue($adapter, $tax);

        $prices = (new \ReflectionMethod(Simple::class, 'getProductPrices'))->invoke($adapter, $product);
        $this->assertEqualsWithDelta($expected, $prices['p_excl_tax'], 0.0001);
    }

    public static function optionCases(): array
    {
        return [
            'free first' => [[[0, 0, false], [350, 350, false], [450, 450, false]], 1099.99],
            'free middle' => [[[350, 350, false], [0, 0, false], [450, 450, false]], 1099.99],
            'free last' => [[[450, 450, false], [350, 350, false], [0, 0, false]], 1099.99],
            'default first' => [[[350, 350, true], [100, 100, false]], 1449.99],
            'default last' => [[[100, 100, false], [350, 350, true]], 1449.99],
            'free default first' => [[[0, 0, true], [100, 100, false]], 1099.99],
            'converted percentage' => [[[10, 109.999, false]], 1209.989],
            'paid minimum' => [[[450, 450, false], [350, 350, false]], 1449.99],
            'optional add-on' => [[[350, 350, false]], 1099.99, false],
        ];
    }

    public function testNativePercentageUsesChildPriceWithoutChangingParentOption(): void
    {
        $child = $this->getMockBuilder(Product::class)->disableOriginalConstructor()
            ->onlyMethods(['getPrice', 'getFinalPrice', 'getStore', 'getPriceInfo'])->getMock();
        $child->setData('price', 2000.0);
        $child->method('getPrice')->willReturnCallback(fn() => $child->getData('price'));
        $child->method('getFinalPrice')->willReturn(2000.0);
        $child->method('getStore')->willReturn($this->createMock(\Magento\Store\Model\Store::class));
        $parent = $this->getMockBuilder(Product::class)->disableOriginalConstructor()
            ->onlyMethods(['getOptions', 'getPriceInfo'])->getMock();
        foreach ([[$parent, 1000.0], [$child, 2000.0]] as [$product, $base]) {
            $price = $this->createMock(\Magento\Framework\Pricing\Price\PriceInterface::class);
            $price->method('getValue')->willReturn($base);
            $info = $this->createMock(\Magento\Framework\Pricing\PriceInfoInterface::class);
            $info->method('getPrice')->willReturn($price);
            $product->method('getPriceInfo')->willReturn($info);
        }
        $value = $this->getMockBuilder(Value::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $value->setData(['price' => 10, 'price_type' => Value::TYPE_PERCENT]);
        (new \ReflectionProperty(Value::class, 'customOptionPriceCalculator'))
            ->setValue($value, new CustomOptionPriceCalculator());
        $option = $this->getMockBuilder(Option::class)->disableOriginalConstructor()
            ->onlyMethods(['getValues'])->getMock();
        $option->setProduct($parent)->setData('is_require', true);
        $option->method('getValues')->willReturn([$value]);
        $value->setOption($option)->setProduct($parent);
        $parent->method('getOptions')->willReturn([$option]);
        $parentAdapter = $this->createMock(Simple::class);
        $parentAdapter->method('getProduct')->willReturn($parent);
        $adapter = $this->getMockBuilder(Simple::class)->disableOriginalConstructor()
            ->onlyMethods(['convertPrice', 'getPriceByCatalogRules', 'getSingleUnitTierPrice'])->getMock();
        $adapter->setData('parent_adapter', $parentAdapter);
        $adapter->method('convertPrice')->willReturnArgument(0);
        $adapter->method('getPriceByCatalogRules')->willReturn(false);
        $adapter->method('getSingleUnitTierPrice')->willReturn(null);
        $tax = $this->createMock(Catalog::class);
        $tax->method('getTaxPrice')->willReturnArgument(1);
        (new \ReflectionProperty(Simple::class, 'catalogHelper'))->setValue($adapter, $tax);

        $prices = (new \ReflectionMethod(Simple::class, 'getProductPrices'))->invoke($adapter, $child);
        $this->assertEquals(2200.0, $prices['p_excl_tax']);
        $this->assertSame($parent, $option->getProduct());
        $this->assertSame($option, $value->getOption());
        $this->assertSame($parent, $value->getProduct());
    }
}
