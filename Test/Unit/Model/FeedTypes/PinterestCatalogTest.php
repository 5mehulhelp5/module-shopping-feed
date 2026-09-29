<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\FeedTypes;

use MageOS\ShoppingFeed\Model\Feed\Validation\PinterestCatalog;
use MageOS\ShoppingFeed\Model\FeedTypes\Config\Converter;
use MageOS\ShoppingFeed\Model\Generator;
use MageOS\ShoppingFeed\Model\Product\Formatter\ValueMap;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class PinterestCatalogTest extends ModelFramework
{
    private function feedTypes(): array
    {
        $document = new \DOMDocument();
        $document->load(dirname(__DIR__, 4) . '/etc/mageos_shopping_feed.xml');
        $decoder = $this->createMock(DecoderInterface::class);
        $decoder->method('decode')->willReturnCallback(static fn($value) => json_decode($value, true));
        return (new Converter($decoder))->convert($document)['feed'];
    }

    public function testPresetUsesPinterestColumnsAndExistingVariantMappers(): void
    {
        $types = $this->feedTypes();
        self::assertArrayHasKey('pinterest_catalog', $types);
        $pinterest = $types['pinterest_catalog'];
        $defaults = $pinterest['default_feed_config'];
        $columns = $defaults['columns']['product_columns'];
        foreach (['id', 'title', 'description', 'link', 'image_link', 'price', 'availability', 'condition',
            'brand', 'gtin', 'mpn', 'item_group_id', 'color', 'size', 'google_product_category'] as $column) {
            self::assertArrayHasKey($column, $columns);
        }
        foreach (['sku_id', 'promotion_id', 'identifier_exists', 'availability_date', 'item_group_title', 'variant_option'] as $column) {
            self::assertArrayNotHasKey($column, $columns);
        }
        self::assertSame('new', $columns['condition']['param']);
        self::assertSame('sku', $columns['item_group_id']['param']);
        self::assertEmpty($columns['mpn']['param']);
        self::assertEmpty($columns['gtin']['param']);
        self::assertStringNotContainsString('google_shopping', (string)$columns['link']['param']);
        self::assertSame('\t', $defaults['output_params']['delimiter']);
        self::assertSame('UTF-8', $defaults['output_params']['encoding']);
        self::assertStringEndsWith('.tsv', $defaults['file']['feed']);
        self::assertSame('1', $defaults['configurable']['associated_products_mode']);
        self::assertSame('1', $defaults['general']['complex_duplicates_check']);
        self::assertSame('1', $columns['product_type']['param']);
        self::assertSame('500', $defaults['filters']['filters_output_limit'][1]['limit']);
        self::assertSame(
            $types['google_shopping']['directives']['directive_availability']['mappers'],
            $pinterest['directives']['directive_availability']['mappers']
        );
        self::assertArrayNotHasKey('formatters', $types['google_shopping']['directives']['directive_availability']);
    }

    public function testAvailabilityMappingIsConfiguredOnlyForPinterest(): void
    {
        $types = $this->feedTypes();
        $formatter = $types['pinterest_catalog']['directives']['directive_availability']['formatters']['default']['type'];
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
        self::assertSame('out of stock', $mapper->run('backorder'));
        self::assertSame('preorder', $mapper->run('preorder'));
        self::assertSame('out of stock', $mapper->run('discontinued'));
        self::assertSame('in stock', $mapper->run('in stock'));
        self::assertSame('', $mapper->run('unknown'));
    }

    public static function validRow(): array
    {
        return [
            'id' => 'sku-001', 'title' => 'Café "Blue", Shirt', 'description' => 'Cotton shirt',
            'availability' => 'in stock', 'condition' => 'new', 'price' => '29.99 USD',
            'link' => 'https://example.com/shirt', 'image_link' => 'https://example.com/shirt.jpg',
            'brand' => 'Example', 'gtin' => '0123456789012', 'mpn' => '', 'item_group_id' => 'parent-sku',
        ];
    }

    public function testValidRowAndIdentifierWarning(): void
    {
        $validator = new PinterestCatalog();
        self::assertSame(['errors' => [], 'warnings' => []], $validator->validate(self::validRow()));
        $row = self::validRow();
        $row['gtin'] = '';
        $result = $validator->validate($row);
        self::assertSame([], $result['errors']);
        self::assertCount(1, $result['warnings']);
    }

    public function testOptionalIdentifiersAndConditionMayBeEmpty(): void
    {
        $row = self::validRow();
        unset($row['brand'], $row['gtin'], $row['mpn'], $row['condition']);
        self::assertSame([], (new PinterestCatalog())->validate($row)['errors']);
    }

    public function testConfigurableRowsRequireItemGroupId(): void
    {
        $row = self::validRow();
        unset($row['item_group_id']);
        self::assertSame([], (new PinterestCatalog())->validate($row)['errors']);
        self::assertStringContainsString('item_group_id', implode(' ', (new PinterestCatalog())->validate($row, true)['errors']));
    }

    /** @dataProvider invalidRows */
    #[DataProvider('invalidRows')]
    public function testInvalidRowsAreRejected(array $row, string $field): void
    {
        $result = (new PinterestCatalog())->validate($row);
        self::assertNotEmpty($result['errors']);
        self::assertStringContainsString($field, implode(' ', $result['errors']));
    }

    public static function invalidRows(): array
    {
        $cases = [];
        foreach (['id', 'title', 'description', 'availability', 'price', 'link', 'image_link'] as $field) {
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

        foreach (['title' => str_repeat('é', 501), 'description' => str_repeat('é', 10001),
            'id' => "bad\x00id", 'image_link' => 'https://example.com/image,1.jpg',
            'gender' => 'other', 'age_group' => 'teen', 'gtin' => '123456789X',
            'additional_image_link' => 'https://example.com/a.jpg|https://example.com/b.jpg',
            'product_type' => 'A > B > C > D > E > F',
            'item_group_id' => str_repeat('g', 128), 'brand' => str_repeat('b', 101),
            'color' => str_repeat('r', 31), 'link' => 'https://example.com/' . str_repeat('x', 500),
            'variant_values' => 'Red,M'] as $field => $value) {
            $cases['invalid Pinterest ' . $field] = [array_replace(self::validRow(), [$field => $value]), $field];
        }
        $cases['invalid date'] = [array_replace(self::validRow(), [
            'sale_price' => '19.99 USD', 'sale_price_effective_date' => '2026-02-30T0:00/2026-03-01T0:00',
        ]), 'sale_price_effective_date'];
        $cases['sale currency mismatch'] = [array_replace(self::validRow(), ['sale_price' => '20.00 EUR']), 'sale_price'];
        $cases['HTML description'] = [array_replace(self::validRow(), ['description' => '<p>Cotton shirt</p>']), 'description'];
        $cases['overlong id'] = [array_replace(self::validRow(), ['id' => str_repeat('i', 128)]), 'id'];
        return $cases;
    }

    public function testCategoryFormatterAndOpenEndedSale(): void
    {
        $formatter = new \MageOS\ShoppingFeed\Model\Product\Formatter\CategoryPath(5);
        self::assertSame('A > B > C > D > E', $formatter->run('A > B > C > D > E > F'));
        self::assertSame('A > B', $formatter->run(' A > B '));
        self::assertSame('', $formatter->run(null));
        $validator = new PinterestCatalog();
        $row = self::validRow() + ['sale_price' => '19.99 USD'];
        self::assertSame([], $validator->validate($row)['errors']);
        self::assertStringContainsString('no effective date', implode(' ', $validator->validate($row)['warnings']));
        $row['sale_price_effective_date'] = '2026-09-01T00:00:00.000Z/2026-10-31T23:59:00.000Z';
        self::assertSame([], $validator->validate($row)['errors']);
        $row['sale_price_effective_date'] = '2026-09-01T00:00:00-04:00/2026-10-31T00:00:00+00:00';
        self::assertSame([], $validator->validate($row)['errors']);
    }

    public function testGeneratorWritesQuotedTsvAndKeepsIdHeader(): void
    {
        $types = $this->feedTypes();
        $defaults = $types['pinterest_catalog']['default_feed_config'];
        $columns = array_values($defaults['columns']['product_columns']);
        usort($columns, static fn($left, $right) => $left['order'] <=> $right['order']);
        $settings = [];
        foreach ($defaults as $group => $values) {
            foreach ($values as $key => $value) {
                $settings[$group . '_' . $key] = $value;
            }
        }
        $this->feedMock->method('getData')->willReturnCallback(static fn($key) => $key === 'type' ? 'pinterest_catalog' : null);
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
            'pinterestCatalogValidator' => new PinterestCatalog(),
        ]);
        $generator->setData('temporary_handle', 'handle');
        $write = new \ReflectionMethod($generator, 'writeFeed');
        $headers = array_column($columns, 'column');
        $write->invoke($generator, array_combine($headers, $headers), false);
        $row = self::validRow() + ['additional_image_link' => 'https://example.com/a.jpg,https://example.com/b.jpg', 'sale_price' => '29.99 USD', 'sale_price_effective_date' => 'unused'];
        $write->invoke($generator, $row);
        $lines = explode(PHP_EOL, $written);
        self::assertCount(2, $lines);
        self::assertSame($headers, str_getcsv($lines[0], "\t", '"', ''));
        $values = str_getcsv($lines[1], "\t", '"', '');
        self::assertCount(count($headers), $values);
        $output = array_combine($headers, $values);
        self::assertSame($row['title'], $output['title']);
        self::assertSame($row['additional_image_link'], $output['additional_image_link']);
        self::assertSame('in stock', $output['availability']);
        self::assertSame('', $output['sale_price']);
        self::assertSame('', $output['sale_price_effective_date']);
        self::assertSame('sku-001', $output['id']);
        self::assertSame('parent-sku', $output['item_group_id']);
    }

    public function testTestFeedRejectsInvalidConditionAndLogsWhy(): void
    {
        $this->feedMock->method('getData')->willReturnCallback(static fn($key) => $key === 'type' ? 'pinterest_catalog' : null);
        $this->fileDriverMock->expects(self::never())->method('fileWrite');
        $this->loggerMock->expects(self::once())->method('warning')->with(self::stringContains('condition'));
        $generator = (new ObjectManager($this))->getObject(Generator::class, [
            'feed' => $this->feedMock, 'fileDriver' => $this->fileDriverMock, 'logger' => $this->loggerMock,
            'pinterestCatalogValidator' => new PinterestCatalog(), 'testSku' => 'sku-001',
        ]);
        (new \ReflectionMethod($generator, 'writeFeed'))->invoke(
            $generator, array_replace(self::validRow(), ['condition' => 'damaged'])
        );
        self::assertSame(1, (new \ReflectionProperty($generator, 'countProductsSkipped'))->getValue($generator));
        self::assertSame(0, (new \ReflectionProperty($generator, 'countProductsExported'))->getValue($generator));
    }

    public function testGeneratorRequiresGroupingForConfigurableOutput(): void
    {
        $this->feedMock->method('getData')->willReturnCallback(static fn($key) => $key === 'type' ? 'pinterest_catalog' : null);
        $this->loggerMock->expects(self::once())->method('warning')->with(self::stringContains('item_group_id'));
        $this->fileDriverMock->expects(self::never())->method('fileWrite');
        $generator = (new ObjectManager($this))->getObject(Generator::class, [
            'feed' => $this->feedMock, 'fileDriver' => $this->fileDriverMock, 'logger' => $this->loggerMock,
            'pinterestCatalogValidator' => new PinterestCatalog(), 'testSku' => 'parent-sku',
        ]);
        $row = self::validRow();
        unset($row['item_group_id']);
        $adapter = $this->getMockBuilder(\MageOS\ShoppingFeed\Model\Product\Adapter\Type\Configurable::class)
            ->disableOriginalConstructor()->onlyMethods(['map', 'isSkipped'])->getMock();
        $adapter->method('map')->willReturn([$row]);
        (new \ReflectionMethod($generator, 'addProductToFeed'))->invoke($generator, $adapter);
        self::assertSame(1, (new \ReflectionProperty($generator, 'countProductsSkipped'))->getValue($generator));
        self::assertSame(0, (new \ReflectionProperty($generator, 'countProductsExported'))->getValue($generator));
    }

    public function testStockFilteringRunsBeforePinterestFormatting(): void
    {
        $pinterest = $this->feedTypes()['pinterest_catalog'];
        $directive = $pinterest['directives']['directive_availability'];
        $this->feedMock->method('getData')->willReturnCallback(static fn($key) => $key === 'type' ? 'pinterest_catalog' : null);
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
            ['id' => 'in', 'availability' => 'in_stock'], ['id' => 'out', 'availability' => 'out_of_stock'],
        ]);
        self::assertSame([['id' => 'in', 'availability' => 'in stock']], $rows);
    }
}
