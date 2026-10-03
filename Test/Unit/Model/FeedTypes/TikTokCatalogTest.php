<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\FeedTypes;

use MageOS\ShoppingFeed\Model\Feed\Validation\TikTokCatalog;
use MageOS\ShoppingFeed\Model\FeedTypes\Config\Converter;
use MageOS\ShoppingFeed\Model\Generator;
use MageOS\ShoppingFeed\Model\Product\Formatter\ValueMap;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class TikTokCatalogTest extends ModelFramework
{
    private function feedTypes(): array
    {
        $document = new \DOMDocument();
        $document->load(dirname(__DIR__, 4) . '/etc/mageos_shopping_feed.xml');
        $decoder = $this->createMock(DecoderInterface::class);
        $decoder->method('decode')->willReturnCallback(static fn($value) => json_decode($value, true));
        return (new Converter($decoder))->convert($document)['feed'];
    }

    public function testPresetUsesTikTokColumnsAndExistingVariantMappers(): void
    {
        $types = $this->feedTypes();
        self::assertArrayHasKey('tiktok_catalog', $types);
        $tiktok = $types['tiktok_catalog'];
        $defaults = $tiktok['default_feed_config'];
        $columns = $defaults['columns']['product_columns'];
        foreach (['sku_id', 'title', 'description', 'link', 'image_link', 'price', 'availability', 'condition',
            'brand', 'gtin', 'mpn', 'item_group_id', 'color', 'size', 'google_product_category'] as $column) {
            self::assertArrayHasKey($column, $columns);
        }
        foreach (['id', 'promotion_id', 'identifier_exists', 'availability_date', 'item_group_title', 'variant_option'] as $column) {
            self::assertArrayNotHasKey($column, $columns);
        }
        self::assertSame('new', $columns['condition']['param']);
        self::assertSame('sku', $columns['item_group_id']['param']);
        self::assertEmpty($columns['mpn']['param']);
        self::assertEmpty($columns['gtin']['param']);
        self::assertStringNotContainsString('google_shopping', (string)$columns['link']['param']);
        self::assertSame(',', $defaults['output_params']['delimiter']);
        self::assertSame('UTF-8', $defaults['output_params']['encoding']);
        self::assertStringEndsWith('.csv', $defaults['file']['feed']);
        self::assertSame('1', $defaults['configurable']['associated_products_mode']);
        self::assertSame('1', $defaults['general']['complex_duplicates_check']);
        self::assertSame('1', $columns['product_type']['param']);
        self::assertArrayHasKey('video_link', $columns);
        self::assertSame(',', $tiktok['directives']['directive_additional_image_link']['mappers']['default']['configuration']['separator']);
        self::assertStringContainsString('TikTok', $tiktok['directives']['directive_additional_image_link']['mappers']['default']['type']);
        foreach (['directive_product_type_magento_category', 'directive_google_category_by_category'] as $name) {
            self::assertSame(\MageOS\ShoppingFeed\Model\Product\Formatter\CategoryPath::class,
                $tiktok['directives'][$name]['formatters']['default']['type']);
        }
        self::assertSame(
            $types['google_shopping']['directives']['directive_availability']['mappers'],
            $tiktok['directives']['directive_availability']['mappers']
        );
        self::assertArrayNotHasKey('formatters', $types['google_shopping']['directives']['directive_availability']);
    }

    public function testAvailabilityMappingIsConfiguredOnlyForTikTok(): void
    {
        $types = $this->feedTypes();
        $formatter = $types['tiktok_catalog']['directives']['directive_availability']['formatters']['default']['type'];
        $document = new \DOMDocument();
        $document->load(dirname(__DIR__, 4) . '/etc/di.xml');
        $xpath = new \DOMXPath($document);
        $values = [];
        foreach ($xpath->query('/config/virtualType[@name="' . $formatter . '"]/arguments/argument/item') as $item) {
            $values[$item->getAttribute('name')] = $item->textContent;
        }
        $mapper = new ValueMap($values);
        self::assertSame('in stock', $mapper->run('in_stock'));
        self::assertSame('out of stock', $mapper->run('out_of_stock'));
        self::assertSame('available for order', $mapper->run('backorder'));
        self::assertSame('preorder', $mapper->run('preorder'));
        self::assertSame('discontinued', $mapper->run('discontinued'));
        self::assertSame('in stock', $mapper->run('in stock'));
        self::assertSame('', $mapper->run('unknown'));
    }

    public static function validRow(): array
    {
        return [
            'sku_id' => 'sku-001', 'title' => 'Café "Blue" Shirt', 'description' => 'Cotton shirt',
            'availability' => 'in stock', 'condition' => 'new', 'price' => '29.99 USD',
            'link' => 'https://example.com/shirt', 'image_link' => 'https://example.com/shirt.jpg',
            'brand' => 'Example', 'gtin' => '0123456789012', 'mpn' => '', 'item_group_id' => 'parent-sku',
            'google_product_category' => 'Apparel & Accessories > Clothing > Shirts & Tops',
        ];
    }

    public function testValidRowAndIdentifierWarning(): void
    {
        $validator = new TikTokCatalog();
        self::assertSame(['errors' => [], 'warnings' => []], $validator->validate(self::validRow()));
        $row = self::validRow();
        $row['gtin'] = '';
        $result = $validator->validate($row);
        self::assertSame([], $result['errors']);
        self::assertCount(1, $result['warnings']);
    }

    public function testMissingTaxonomyWarnsWithoutRejectingTheProduct(): void
    {
        $row = self::validRow();
        $row['google_product_category'] = '  ';
        $row['product_type'] = 'Our Store > Clothing';
        $result = (new TikTokCatalog())->validate($row);
        self::assertSame([], $result['errors']);
        self::assertStringContainsString('google_product_category', implode(' ', $result['warnings']));
    }

    /** @dataProvider invalidRows */
    #[DataProvider('invalidRows')]
    public function testInvalidRowsAreRejected(array $row, string $field): void
    {
        $result = (new TikTokCatalog())->validate($row);
        self::assertNotEmpty($result['errors']);
        self::assertStringContainsString($field, implode(' ', $result['errors']));
    }

    public static function invalidRows(): array
    {
        $cases = [];
        foreach (['sku_id', 'title', 'description', 'availability', 'condition', 'price', 'link', 'image_link', 'brand'] as $field) {
            $row = self::validRow();
            unset($row[$field]);
            $cases['missing ' . $field] = [$row, $field];
        }
        foreach (['condition' => 'damaged', 'availability' => 'in_stock', 'price' => '29,99 USD',
            'sale_price' => 'USD 20', 'link' => 'javascript:alert(1)', 'image_link' => '/image.jpg'] as $field => $value) {
            $cases['invalid ' . $field] = [array_replace(self::validRow(), [$field => $value]), $field];
        }
        $cases['whitespace title'] = [array_replace(self::validRow(), ['title' => '  ']), 'title'];
        $cases['repeated required field'] = [array_replace(self::validRow(), ['price' => ['29.99 USD']]), 'price'];

        foreach (['title' => str_repeat('é', 151), 'description' => str_repeat('é', 20001),
            'sku_id' => "bad\x00id", 'image_link' => 'https://example.com/image.webp',
            'gender' => 'other', 'age_group' => 'teen', 'gtin' => '123456789X',
            'additional_image_link' => 'https://example.com/a.jpg|https://example.com/b.jpg',
            'google_product_category' => 'A > B > C > D'] as $field => $value) {
            $cases['invalid TikTok ' . $field] = [array_replace(self::validRow(), [$field => $value]), $field];
        }
        $cases['invalid date'] = [array_replace(self::validRow(), [
            'sale_price' => '19.99 USD', 'sale_price_effective_date' => '2026-02-30T0:00/2026-03-01T0:00',
        ]), 'sale_price_effective_date'];
        $cases['sale currency mismatch'] = [array_replace(self::validRow(), ['sale_price' => '20.00 EUR']), 'sale_price'];
        return $cases;
    }

    public function testCategoryFormatterAndOpenEndedSale(): void
    {
        $formatter = new \MageOS\ShoppingFeed\Model\Product\Formatter\CategoryPath();
        self::assertSame('A > B > C', $formatter->run('A > B > C > D'));
        self::assertSame('A > B', $formatter->run(' A > B '));
        self::assertSame('', $formatter->run(null));
        $validator = new TikTokCatalog();
        $row = self::validRow() + ['sale_price' => '19.99 USD'];
        self::assertSame([], $validator->validate($row)['errors']);
        $row['sale_price_effective_date'] = '2026-09-01T0:00/2026-10-31T23:59';
        self::assertSame([], $validator->validate($row)['errors']);
        $row['sale_price_effective_date'] = '2026-09-01T00:00:00-04:00/2026-10-31T00:00:00+00:00';
        self::assertSame([], $validator->validate($row)['errors']);
    }

    public function testGeneratorWritesQuotedCsvAndKeepsSkuIdHeader(): void
    {
        $types = $this->feedTypes();
        $defaults = $types['tiktok_catalog']['default_feed_config'];
        $columns = array_values($defaults['columns']['product_columns']);
        usort($columns, static fn($left, $right) => $left['order'] <=> $right['order']);
        $settings = [];
        foreach ($defaults as $group => $values) {
            foreach ($values as $key => $value) {
                $settings[$group . '_' . $key] = $value;
            }
        }
        $this->feedMock->method('getData')->willReturnCallback(static fn($key) => $key === 'type' ? 'tiktok_catalog' : null);
        $this->feedMock->method('getColumnsMap')->willReturn($columns);
        $this->feedMock->method('getConfig')->willReturnCallback(static fn($key, $default = '') => $settings[$key] ?? $default);
        $written = '';
        $this->fileDriverMock->method('fileWrite')->willReturnCallback(
            static function ($handle, $data) use (&$written) {
                $written .= $data;
                return strlen($data);
            }
        );
        $generator = (new ObjectManager($this))->getObject(Generator::class, [
            'feed' => $this->feedMock, 'fileDriver' => $this->fileDriverMock, 'logger' => $this->loggerMock,
            'tikTokCatalogValidator' => new TikTokCatalog(),
        ]);
        $generator->setData('temporary_handle', 'handle');
        $write = new \ReflectionMethod($generator, 'writeFeed');
        $headers = array_column($columns, 'column');
        $write->invoke($generator, array_combine($headers, $headers), false);
        $row = self::validRow() + ['additional_image_link' => 'https://example.com/a.jpg,https://example.com/b.jpg', 'sale_price' => '29.99 USD', 'sale_price_effective_date' => 'unused'];
        $write->invoke($generator, $row);
        $lines = explode(PHP_EOL, $written);
        self::assertCount(2, $lines);
        self::assertSame($headers, str_getcsv($lines[0], ',', '"', ''));
        $values = str_getcsv($lines[1], ',', '"', '');
        self::assertCount(count($headers), $values);
        $output = array_combine($headers, $values);
        self::assertSame($row['title'], $output['title']);
        self::assertSame($row['additional_image_link'], $output['additional_image_link']);
        self::assertSame('in stock', $output['availability']);
        self::assertSame('', $output['sale_price']);
        self::assertSame('', $output['sale_price_effective_date']);
        self::assertSame('sku-001', $output['sku_id']);
        self::assertSame('parent-sku', $output['item_group_id']);
    }

    public function testTestFeedRejectsInvalidConditionAndLogsWhy(): void
    {
        $this->feedMock->method('getData')->willReturnCallback(static fn($key) => $key === 'type' ? 'tiktok_catalog' : null);
        $this->fileDriverMock->expects(self::never())->method('fileWrite');
        $this->loggerMock->expects(self::once())->method('warning')->with(self::stringContains('condition'));
        $generator = (new ObjectManager($this))->getObject(Generator::class, [
            'feed' => $this->feedMock, 'fileDriver' => $this->fileDriverMock, 'logger' => $this->loggerMock,
            'tikTokCatalogValidator' => new TikTokCatalog(), 'testSku' => 'sku-001',
        ]);
        (new \ReflectionMethod($generator, 'writeFeed'))->invoke(
            $generator, array_replace(self::validRow(), ['condition' => 'damaged'])
        );
        self::assertSame(1, (new \ReflectionProperty($generator, 'countProductsSkipped'))->getValue($generator));
        self::assertSame(0, (new \ReflectionProperty($generator, 'countProductsExported'))->getValue($generator));
    }

    public function testStockFilteringRunsBeforeTikTokFormatting(): void
    {
        $tiktok = $this->feedTypes()['tiktok_catalog'];
        $directive = $tiktok['directives']['directive_availability'];
        $this->feedMock->method('getData')->willReturnCallback(static fn($key) => $key === 'type' ? 'tiktok_catalog' : null);
        $this->feedMock->method('getColumnsMap')->willReturn([
            ['column' => 'availability', 'attribute' => 'directive_availability'],
        ]);
        $this->feedMock->method('getConfig')->willReturn(false);
        $this->feedTypesConfigMock->method('isAllowedDirective')->willReturn(true);
        $this->feedTypesConfigMock->method('getDirective')->willReturn($directive);
        $this->mapperFactoryMock->method('getMapperData')->willReturn($directive['mappers']['default']);
        $adapter = $this->getMockBuilder(\MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple::class)
            ->disableOriginalConstructor()->onlyMethods(['setSkipProduct'])->getMock();
        $mapper = (new ObjectManager($this))->getObject(
            \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Availability::class
        );
        $mapper->addAdapter($adapter);
        $this->mapperFactoryMock->method('create')->willReturn($mapper);
        $formatterFactory = $this->createMock(\MageOS\ShoppingFeed\Model\Product\Formatter\FormatterFactory::class);
        $formatterFactory->method('getFormatterData')->willReturn($directive['formatters']['default']);
        $formatterFactory->method('create')->willReturn(new ValueMap(['in_stock' => 'in stock']));
        foreach (['feed' => $this->feedMock, 'feedTypesConfig' => $this->feedTypesConfigMock,
            'mapperFactory' => $this->mapperFactoryMock, 'formatterFactory' => $formatterFactory] as $property => $value) {
            (new \ReflectionProperty($adapter, $property))->setValue($adapter, $value);
        }
        $adapter->expects(self::once())->method('setSkipProduct')->with('filtered by column "availability"');
        $rows = (new \ReflectionMethod($adapter, 'filterAndFormatRows'))->invoke($adapter, [
            ['sku_id' => 'in', 'availability' => 'in_stock'], ['sku_id' => 'out', 'availability' => 'out_of_stock'],
        ]);
        self::assertSame([['sku_id' => 'in', 'availability' => 'in stock']], $rows);
    }
}
