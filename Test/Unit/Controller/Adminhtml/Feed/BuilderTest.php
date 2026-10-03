<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Test\Unit\Controller\Adminhtml\Feed;

use MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Builder;
use MageOS\ShoppingFeed\Model\Feed;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class BuilderTest extends TestCase
{
    private function builder($factory): Builder
    {
        return new Builder($factory, $this->createMock(\Psr\Log\LoggerInterface::class),
            $this->createMock(\Magento\Framework\Registry::class),
            $this->createMock(\Magento\Backend\Model\Session::class));
    }

    /** @dataProvider invalidInput */
    #[DataProvider('invalidInput')]
    public function testRejectsMalformedInputBeforeLoadingAFeed(array $input): void
    {
        $factory = $this->createMock(\MageOS\ShoppingFeed\Model\FeedFactory::class);
        $factory->expects(self::never())->method('create');
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->builder($factory)->build($input);
    }

    public static function invalidInput(): array
    {
        return array_map(static fn($input) => [$input], [
            ['id'=>['1']], ['id'=>'5abc'], ['id'=>'5 OR 1=1'], ['id'=>-1],
            ['id'=>'999999999999999999999999999'], ['store_id'=>[]], ['store_id'=>'1abc'],
            ['use_microdata'=>[]], ['use_microdata'=>'yes'], ['use_microdata'=>2], ['type'=>[]],
        ]);
    }

    public function testNormalizesValidNumericFields(): void
    {
        $feed = $this->getMockBuilder(Feed::class)->disableOriginalConstructor()
            ->onlyMethods(['load', 'getSchedules', 'isObjectNew'])->getMock();
        $feed->expects(self::once())->method('load')->with(self::identicalTo(5))->willReturnSelf();
        $factory = $this->createMock(\MageOS\ShoppingFeed\Model\FeedFactory::class);
        $factory->method('create')->willReturn($feed);
        $result = $this->builder($factory)->build(['id'=>'5','store_id'=>'2','use_microdata'=>'0']);
        self::assertSame(2, $result->getStoreId());
        self::assertSame(0, $result->getUseMicrodata());
    }

    /** @dataProvider feedTypes */
    #[DataProvider('feedTypes')]
    public function testOnlyGoogleShoppingDefaultsNewFeedsToMicrodata(string $type, int $expected): void
    {
        $feed = $this->getMockBuilder(Feed::class)->disableOriginalConstructor()
            ->onlyMethods(['setType', 'getSchedules', 'isObjectNew'])->getMock();
        $factory = $this->createMock(\MageOS\ShoppingFeed\Model\FeedFactory::class);
        $factory->method('create')->willReturn($feed);
        self::assertSame($expected, $this->builder($factory)->build(['type' => $type])->getUseMicrodata());
    }

    public static function feedTypes(): array
    {
        return [['google_shopping', 1], ['generic', 0], ['google_local_inventory', 0],
            ['meta_catalog', 0], ['microsoft_merchant_center', 0], ['tiktok_catalog', 0],
            ['pinterest_catalog', 0], ['openai_google_compatible', 0]];
    }

    public function testExistingMicrodataSelectionsArePreserved(): void
    {
        foreach ([0, 1] as $selection) {
            $feed = $this->getMockBuilder(Feed::class)->disableOriginalConstructor()
                ->onlyMethods(['load', 'getSchedules', 'isObjectNew'])->getMock();
            $feed->method('load')->willReturnCallback(function () use ($feed, $selection) {
                return $feed->setData('use_microdata', $selection);
            });
            $factory = $this->createMock(\MageOS\ShoppingFeed\Model\FeedFactory::class);
            $factory->method('create')->willReturn($feed);
            self::assertSame($selection, $this->builder($factory)->build(['id' => 7])->getUseMicrodata());
        }
    }
}
