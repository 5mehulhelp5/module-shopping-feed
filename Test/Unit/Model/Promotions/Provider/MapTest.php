<?php

namespace MageOS\ShoppingFeed\Test\Unit\Model\Promotions\Provider;

use MageOS\ShoppingFeed\Model\Promotions\Provider\Map;
use MageOS\ShoppingFeed\Test\Unit\CompatibilityTestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class MapTest extends CompatibilityTestCase
{
    private function minimumMap(): Map
    {
        $map = new Map(new \Magento\Framework\Serialize\Serializer\Json());
        $provider = $this->createMock(\MageOS\ShoppingFeed\Model\Promotions\Provider::class);
        $feed = $this->createMock(\MageOS\ShoppingFeed\Model\Feed::class);
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->method('getData')->with('current_currency')->willReturn(new \Magento\Framework\DataObject(['code'=>'USD']));
        $feed->method('getStore')->willReturn($store);
        $provider->method('getFeed')->willReturn($feed);
        return $map->setProvider($provider);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('conditionTrees')]
    public function testMinimumAmountRespectsConditionTree(array $tree, string $expected): void
    {
        $rule = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()->onlyMethods([])->getMock();
        $rule->setData('conditions_serialized', json_encode($tree));
        self::assertSame($expected, $this->minimumMap()->mapMinimumPurchaseAmount($rule));
    }

    public static function conditionTrees(): array
    {
        $subtotal = static fn($amount, $operator='>=') => ['attribute'=>'base_subtotal','operator'=>$operator,'value'=>$amount];
        $combine = static fn($children, $aggregator='all', $value='1') => [
            'type'=>\Magento\SalesRule\Model\Rule\Condition\Combine::class,
            'aggregator'=>$aggregator,'value'=>$value,'conditions'=>$children];
        $country = ['attribute'=>'country_id','operator'=>'==','value'=>'US'];
        return [
            'root subtotal' => [$combine([$subtotal(50)]), '50.00 USD'],
            'nested all' => [$combine([$country, $combine([$subtotal(50), $subtotal(75)])]), '75.00 USD'],
            'any subtotal' => [$combine([$subtotal(50), $subtotal(75)], 'any'), '50.00 USD'],
            'any non-subtotal alternative' => [$combine([$subtotal(50), $country], 'any'), ''],
            'negated combination' => [$combine([$subtotal(50)], 'all', '0'), ''],
            'upper bound' => [$combine([$subtotal(50, '<=')]), ''],
            'strict lower bound' => [$combine([$subtotal(50, '>')]), '50.01 USD'],
            'empty' => [[], ''],
            'incomplete leaf' => [$combine([['attribute'=>'base_subtotal']]), ''],
        ];
    }

    public function testIncompleteDateConfigurationIsOmitted(): void
    {
        $map = $this->minimumMap();
        self::assertSame('', $map->mapEffectiveDates());
        self::assertSame('', $map->mapDisplayDates(['display'=>['from'=>'2026-09-30']]));
        self::assertSame('', $map->mapDates(['from'=>[], 'to'=>'2026-10-01']));
    }

    public function testMalformedSerializedConditionsFailWithAnActionableError(): void
    {
        $rule = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()->onlyMethods([])->getMock();
        $rule->setData('conditions_serialized', 'a:1:{invalid legacy data}');
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('conditions');
        $this->minimumMap()->mapMinimumPurchaseAmount($rule);
    }

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
