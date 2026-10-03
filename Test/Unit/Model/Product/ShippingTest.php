<?php

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product;

use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Product\Shipping;
use MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ShippingTest extends TestCase
{
    /** @dataProvider shippingMethods */
    #[DataProvider('shippingMethods')]
    public function testAllowedCarriersHandleUnconfiguredMethods($methods, array $expected): void
    {
        $feed = $this->createMock(Feed::class);
        $feed->method('getConfig')->willReturnCallback(
            static fn ($key) => $key === 'shipping_methods' ? $methods : ['ups']
        );
        $adapter = $this->createMock(Simple::class);
        $adapter->method('getFeed')->willReturn($feed);
        $shipping = (new \ReflectionClass(Shipping::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Shipping::class, 'adapter'))->setValue($shipping, $adapter);
        self::assertSame($expected, array_values($shipping->getAllowedCarriers()));
    }

    public static function shippingMethods(): array
    {
        return [
            [null, []], ['', []], ['flatrate_flatrate', []],
            [[[], 123, 'invalid', null], []],
            [['flatrate_flatrate', 'ups_03', 'flatrate_other', 'freeshipping_freeshipping'],
                ['flatrate', 'freeshipping']],
        ];
    }
}
