<?php

namespace MageOS\ShoppingFeed\Test\Unit\Model\Promotions\Provider;

use MageOS\ShoppingFeed\Model\Promotions\Provider\Map;
use MageOS\ShoppingFeed\Test\Unit\CompatibilityTestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class MapTest extends CompatibilityTestCase
{
    public function testUsesCurrentGooglePromotionEnumValues(): void
    {
        $this->assertSame('all_products', Map::APPLICABILITY_ALL);
        $this->assertSame('specific_products', Map::APPLICABILITY_SPECIFIC);
        $this->assertSame('generic_code', Map::GENERIC_CODE);
        $this->assertSame('no_code', Map::NO_CODE);
    }

    public function testMapsCouponTypeToCurrentOfferType(): void
    {
        $serializer = $this->createMock('Magento\Framework\Serialize\SerializerInterface');
        $map = new Map($serializer);
        $rule = $this->createCompatibleMock(
            'Magento\SalesRule\Model\Rule',
            ['getCouponType']
        );
        $rule->method('getCouponType')->willReturnOnConsecutiveCalls(1, 2);

        $this->assertSame('no_code', $map->mapOfferType($rule));
        $this->assertSame('generic_code', $map->mapOfferType($rule));
    }
}
