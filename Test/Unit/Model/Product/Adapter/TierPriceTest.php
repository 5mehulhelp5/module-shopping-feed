<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Adapter;

use MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple;
use MageOS\ShoppingFeed\Model\Product\Helper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Price;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class TierPriceTest extends TestCase
{
    /** @dataProvider tierCases */
    #[DataProvider('tierCases')]
    public function testGuestSingleUnitDiscount(array $rows, bool $onSale, float $expected): void
    {
        $product = $this->product($rows);
        $adapter = $this->getMockBuilder(Simple::class)->disableOriginalConstructor()
            ->onlyMethods(['hasPriceByCatalogRules', 'getPriceByCatalogRules', 'convertPrice'])->getMock();
        $adapter->method('hasPriceByCatalogRules')->willReturn(false);
        $adapter->method('getPriceByCatalogRules')->willReturn(false);
        $adapter->method('convertPrice')->willReturnArgument(0);
        (new \ReflectionProperty(Simple::class, 'product'))->setValue($adapter, $product);
        (new \ReflectionProperty(Simple::class, 'helper'))->setValue($adapter, $this->createMock(Helper::class));
        (new \ReflectionProperty(Simple::class, 'localeResolver'))->setValue(
            $adapter, $this->createMock(\Magento\Framework\Locale\Resolver::class)
        );
        $tax = $this->createMock(\MageOS\ShoppingFeed\Model\Product\Helper\Catalog::class);
        $tax->method('getTaxPrice')->willReturnArgument(1);
        (new \ReflectionProperty(Simple::class, 'catalogHelper'))->setValue($adapter, $tax);

        $this->assertSame($onSale, $adapter->hasSpecialPrice());
        $prices = (new \ReflectionMethod(Simple::class, 'getProductPrices'))->invoke($adapter, $product);
        $this->assertEquals($expected, $prices['sp_excl_tax']);
        $this->assertEquals(117, $prices['p_excl_tax']);
        $this->assertFalse($adapter->getSalePriceEffectiveDates(), 'Tier-only discounts have no invented date range');
        $this->assertSame(2, $product->getCustomerGroupId(), 'Guest pricing must not change another directive\'s group');
    }

    public static function tierCases(): array
    {
        return [
            'guest qty one' => [[[0, 1, 108]], true, 108],
            'all groups qty one' => [[[32000, 1, 108]], true, 108],
            'bulk only' => [[[0, 10, 80]], false, 117],
            'another customer group' => [[[2, 1, 70]], false, 117],
            'qty one and cheaper bulk' => [[[0, 1, 108], [0, 10, 80]], true, 108],
            'no tiers' => [[], false, 117],
        ];
    }

    public function testTierDirectiveUsesOneUnitAndPreservesCustomerGroup(): void
    {
        $product = $this->product([[0, 1, 108], [0, 10, 80], [2, 1, 70]]);
        $adapter = $this->createMock(Simple::class);
        $adapter->method('getProduct')->willReturn($product);
        $mapper = new \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\TierPrice(
            $this->createMock(\MageOS\ShoppingFeed\Model\Logger::class)
        );
        $mapper->addAdapter($adapter);
        $this->assertSame('108.00', $mapper->map());
        $this->assertSame(2, $product->getCustomerGroupId());
        $this->assertSame('70.00', $mapper->map(['param' => 2]));
        $this->assertSame(2, $product->getCustomerGroupId());
    }

    public function testTierDirectiveDoesNotPublishBulkOnlyPrice(): void
    {
        $adapter = $this->createMock(Simple::class);
        $adapter->method('getProduct')->willReturn($this->product([[0, 10, 80]]));
        $mapper = new \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\TierPrice(
            $this->createMock(\MageOS\ShoppingFeed\Model\Logger::class)
        );
        $mapper->addAdapter($adapter);
        $this->assertSame('', $mapper->map());
    }

    private function product(array $rows): Product
    {
        $price = $this->getMockBuilder(Price::class)->disableOriginalConstructor()
            ->onlyMethods(['getAllCustomerGroupsId'])->getMock();
        $price->method('getAllCustomerGroupsId')->willReturn(32000);
        $product = $this->getMockBuilder(Product::class)->disableOriginalConstructor()
            ->onlyMethods(['getPriceModel', 'getFinalPrice', 'getStore'])->getMock();
        $product->method('getPriceModel')->willReturn($price);
        $product->method('getFinalPrice')->willReturn(117);
        $product->method('getStore')->willReturn($this->createMock(\Magento\Store\Model\Store::class));
        $product->setData([
            'price' => 117, 'customer_group_id' => 2,
            'tier_price' => array_map(static fn($row) => [
                'cust_group' => $row[0], 'price_qty' => $row[1], 'price' => $row[2],
                'website_price' => $row[2], 'website_id' => 0,
            ], $rows),
        ]);
        return $product;
    }
}
