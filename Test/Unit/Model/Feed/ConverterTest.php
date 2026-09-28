<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Test\Unit\Model\Feed;

use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Feed\Converter;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ConverterTest extends TestCase
{
    public function testLegacyGroupingKeepsSkuIdentityWhenOpeningAndSaving(): void
    {
        $rows = [
            ['attribute' => 'directive_item_group_id', 'param' => '0'],
            ['attribute' => 'directive_item_group_id', 'param' => 'entity_id'],
            ['attribute' => 'directive_id', 'param' => '0'],
        ];
        $feed = $this->getMockBuilder(Feed::class)->disableOriginalConstructor()
            ->onlyMethods(['getSchedules', 'getUploads'])->getMock();
        $feed->method('getSchedules')->willReturn([]);
        $feed->method('getUploads')->willReturn([]);
        $feed->setData('config', new DataObject(['columns_product_columns' => $rows]));
        $builder = $this->createMock(\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Builder::class);
        $builder->method('build')->willReturn($feed);
        $converter = new Converter(
            $this->createMock(\Magento\Framework\App\RequestInterface::class),
            $builder,
            $this->createMock(\Magento\Framework\Json\DecoderInterface::class)
        );
        $expected = $rows;
        $expected[0]['param'] = 'sku';
        self::assertSame($expected, $converter->createArrayFromObject($feed)['config_columns_product_columns']);
        $converter->populateFeedData(['config' => ['columns_product_columns' => $rows]]);
        self::assertSame($expected, $feed->getConfig()->getData('columns_product_columns'));
    }
}
