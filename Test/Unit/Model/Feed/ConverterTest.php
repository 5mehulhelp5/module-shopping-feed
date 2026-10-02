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
    #[\PHPUnit\Framework\Attributes\DataProvider('malformedCategories')]
    public function testRejectsMalformedCategoriesWithARecoverableError($categories): void
    {
        [$converter] = $this->categoryConverter();
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $converter->populateFeedData(['config' => ['categories_provider_taxonomy_by_category' => $categories]]);
    }

    public static function malformedCategories(): array
    {
        $cases = [42, false, '{bad', [3 => 'str'], [3 => []], [3 => ['d' => 1]],
            [3 => ['d' => [], 'p' => 0]], [3 => ['d' => 2, 'p' => 0]],
            [3 => ['d' => 1, 'p' => -1]], [3 => ['d' => 1, 'p' => []]],
            [3 => ['d' => 1, 'p' => 0, 'tx' => []]], [3 => ['d' => 1, 'p' => 0, 'ty' => []]],
            ['invalid' => ['d' => 1, 'p' => 0]], [3 => ['id' => 4, 'd' => 1, 'p' => 0]]];
        $result = [];
        foreach ($cases as $case) {
            $result[] = [$case];
            $result[] = [json_encode($case)];
        }
        return $result;
    }

    public function testNewAndLegacyCategoryMapsCarryIdsThroughGeneratorSorting(): void
    {
        $categories = [3 => ['d' => 1, 'p' => 0, 'tx' => 'Furniture', 'ty' => 'Home', 'custom' => false],
            999 => ['id' => 999, 'd' => 0, 'p' => '', 'tx' => '', 'ty' => 'Inactive category']];
        foreach ([$categories, json_encode($categories)] as $input) {
            [$converter, $feed] = $this->categoryConverter();
            $converter->populateFeedData(['config' => ['categories_provider_taxonomy_by_category' => $input]]);
            $stored = $feed->getConfig()->getData('categories_provider_taxonomy_by_category');
            self::assertSame(3, $stored[3]['id']);
            self::assertSame($categories[999], $stored[999]);
            self::assertFalse($stored[3]['custom']);
            self::assertSame(['private' => false], $feed->getConfig()->getData('vendor_setting'));
            array_multisort(array_column($stored, 'p'), SORT_ASC, $stored);
            $mapper = (new \ReflectionClass(
                \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\ProductTypeByCategory::class
            ))->newInstanceWithoutConstructor();
            self::assertSame('Home', $mapper->matchByCategory($stored, [3], 'ty'));
            self::assertSame('Furniture', $mapper->matchByCategory($stored, [3], 'tx'));
        }
    }

    public function testEmptyAndDefaultCategoryMapsRemainSupported(): void
    {
        foreach (['', null, [], '{}', '[]', [3 => ['d' => 1, 'p' => 0, 'tx' => '', 'ty' => '']]] as $input) {
            [$converter, $feed] = $this->categoryConverter();
            $converter->populateFeedData(['config' => ['categories_provider_taxonomy_by_category' => $input]]);
            self::assertSame([], $feed->getConfig()->getData('categories_provider_taxonomy_by_category'));
        }
    }

    private function categoryConverter(): array
    {
        $feed = $this->getMockBuilder(Feed::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $feed->setData('config', new DataObject(['vendor_setting' => ['private' => false]]));
        $builder = $this->createMock(\MageOS\ShoppingFeed\Controller\Adminhtml\Feed\Builder::class);
        $builder->method('build')->willReturn($feed);
        return [new Converter($this->createMock(\Magento\Framework\App\RequestInterface::class), $builder,
            new \Magento\Framework\Json\Decoder(new \Magento\Framework\Serialize\Serializer\Json())), $feed];
    }

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
