<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\FeedTypes;

use MageOS\ShoppingFeed\Model\Feed\Validation\MicrosoftMerchantCenter;
use MageOS\ShoppingFeed\Model\FeedTypes\Config\Converter;
use MageOS\ShoppingFeed\Model\Generator;
use MageOS\ShoppingFeed\Model\Product\Formatter\ValueMap;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class MicrosoftMerchantCenterTest extends ModelFramework
{
    private function feedTypes(): array
    {
        $document = new \DOMDocument();
        $document->load(dirname(__DIR__, 4) . '/etc/mageos_shopping_feed.xml');
        $decoder = $this->createMock(DecoderInterface::class);
        $decoder->method('decode')->willReturnCallback(static fn($value) => json_decode($value, true));
        return (new Converter($decoder))->convert($document)['feed'];
    }

    public function testPresetUsesMicrosoftColumnsAndExistingVariantMappers(): void
    {
        $types = $this->feedTypes();
        self::assertArrayHasKey('microsoft_merchant_center', $types);
        $meta = $types['microsoft_merchant_center'];
        $defaults = $meta['default_feed_config'];
        $columns = $defaults['columns']['product_columns'];
        foreach (['id', 'title', 'description', 'link', 'image_link', 'price', 'availability', 'condition',
            'brand', 'gtin', 'mpn', 'identifier_exists', 'item_group_id', 'color', 'size', 'product_category'] as $column) {
            self::assertArrayHasKey($column, $columns);
        }
        foreach (['promotion_id', 'availability_date', 'item_group_title', 'variant_option', 'google_product_category'] as $column) {
            self::assertArrayNotHasKey($column, $columns);
        }
        self::assertSame('new', $columns['condition']['param']);
        self::assertSame('sku', $columns['item_group_id']['param']);
        self::assertEmpty($columns['mpn']['param']);
        self::assertEmpty($columns['gtin']['param']);
        self::assertStringNotContainsString('google_shopping', (string)$columns['link']['param']);
        self::assertSame('\\t', $defaults['output_params']['delimiter']);
        self::assertSame('UTF-8', $defaults['output_params']['encoding']);
        self::assertStringEndsWith('.txt', $defaults['file']['feed']);
        self::assertSame('', $defaults['output_params']['enclose_cell']);
        self::assertSame('1', $defaults['configurable']['associated_products_mode']);
        self::assertSame('1', $defaults['general']['complex_duplicates_check']);
        self::assertSame(
            $types['google_shopping']['directives']['directive_availability']['mappers'],
            $meta['directives']['directive_availability']['mappers']
        );
        self::assertArrayNotHasKey('formatters', $types['google_shopping']['directives']['directive_availability']);
    }

    public function testAvailabilityMappingIsConfiguredOnlyForMicrosoft(): void
    {
        $types = $this->feedTypes();
        $formatter = $types['microsoft_merchant_center']['directives']['directive_availability']['formatters']['default']['type'];
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
        self::assertSame('in stock', $mapper->run('in stock'));
        self::assertSame('', $mapper->run('unknown'));
    }

    public static function validRow(): array
    {
        return [
            'id' => 'sku-001', 'title' => 'Café "Blue" Shirt', 'description' => 'Cotton shirt',
            'availability' => 'in stock', 'condition' => 'new', 'price' => '29.99 USD',
            'link' => 'https://example.com/shirt', 'image_link' => 'https://example.com/shirt.jpg',
            'brand' => 'Example', 'gtin' => '0123456789012', 'mpn' => '', 'item_group_id' => 'parent-sku',
        ];
    }

    public function testIdentifiersAndDestinationDifferences(): void
    {
        $validator = new MicrosoftMerchantCenter();
        self::assertSame(['errors' => [], 'warnings' => []], $validator->validate(self::validRow()));
        $row = array_replace(self::validRow(), ['gtin' => '', 'mpn' => '', 'brand' => '']);
        self::assertNotEmpty($validator->validate($row)['errors']);
        $row['identifier_exists'] = 'FALSE';
        self::assertSame([], $validator->validate($row)['errors']);
        $row['brand'] = 'Maker';
        self::assertNotEmpty($validator->validate($row)['errors']);
        unset($row['identifier_exists']);
        self::assertNotEmpty($validator->validate($row)['warnings']);
        self::assertSame([], $validator->validate(array_replace(self::validRow(), ['availability' => 'preorder']))['errors']);
        self::assertSame([], $validator->validate(array_replace(self::validRow(), ['title' => str_repeat('é', 150)]))['errors']);
    }

    /** @dataProvider invalidRows */
    #[DataProvider('invalidRows')]
    public function testInvalidRowsAreRejected(array $row, string $field): void
    {
        $result = (new MicrosoftMerchantCenter())->validate($row);
        self::assertNotEmpty($result['errors']);
        self::assertStringContainsString($field, implode(' ', $result['errors']));
    }

    public static function invalidRows(): array
    {
        $cases = [];
        foreach (['id', 'title', 'description', 'availability', 'condition', 'price', 'link', 'image_link'] as $field) {
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
        $cases['API-only availability'] = [array_replace(self::validRow(), ['availability' => 'available for order']), 'availability'];
        $cases['backorder availability'] = [array_replace(self::validRow(), ['availability' => 'backorder']), 'availability'];
        foreach (['id' => str_repeat('é', 51), 'title' => str_repeat('é', 151), 'mpn' => str_repeat('X', 71),
            'price' => '10000000.01 USD', 'item_group_id' => 'sku-001', 'gtin' => 'invented-gtin',
            'description' => "Text\tcolumn", 'color' => ['blue', 'red']] as $field => $value) {
            $cases['out of bounds ' . $field] = [array_replace(self::validRow(), [$field => $value]), $field];
        }
        $cases['bad sale interval'] = [array_replace(self::validRow(), [
            'sale_price' => '19.99', 'sale_price_effective_date' => '2026-02-30T00:00Z/2026-03-02T00:00Z',
        ]), 'sale_price_effective_date'];
        $cases['sale must be numeric'] = [array_replace(self::validRow(), ['sale_price' => '20.00 EUR']), 'sale_price'];
        return $cases;
    }

    public function testGeneratorWritesUtf8TsvAndKeepsHeaderOrder(): void
    {
        $types = $this->feedTypes();
        $defaults = $types['microsoft_merchant_center']['default_feed_config'];
        $columns = array_values($defaults['columns']['product_columns']);
        usort($columns, static fn($left, $right) => $left['order'] <=> $right['order']);
        $settings = [];
        foreach ($defaults as $group => $values) {
            foreach ($values as $key => $value) {
                $settings[$group . '_' . $key] = $value;
            }
        }
        $this->feedMock->method('getData')->willReturnCallback(static fn($key) => $key === 'type' ? 'microsoft_merchant_center' : null);
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
            'microsoftMerchantCenterValidator' => new MicrosoftMerchantCenter(),
        ]);
        $generator->setData('temporary_handle', 'handle');
        $write = new \ReflectionMethod($generator, 'writeFeed');
        $headers = array_column($columns, 'column');
        $write->invoke($generator, array_combine($headers, $headers), false);
        $headers = array_values(array_diff($headers, ['id']));
        $headers[] = 'id';
        $row = self::validRow() + ['sale_price' => '29.99', 'sale_price_effective_date' => ''];
        $write->invoke($generator, $row);
        $lines = explode(PHP_EOL, $written);
        self::assertStringNotContainsString('"id"', $lines[0]);
        self::assertStringNotContainsString("\t" . PHP_EOL, $written);
        self::assertStringEndsWith('sku-001', $written);
        self::assertCount(2, $lines);
        self::assertSame($headers, explode("\t", $lines[0]));
        $values = explode("\t", $lines[1]);
        self::assertCount(count($headers), $values);
        $output = array_combine($headers, $values);
        self::assertSame($row['title'], $output['title']);
        self::assertSame('in stock', $output['availability']);
        self::assertSame('', $output['sale_price']);
        self::assertSame('', $output['sale_price_effective_date']);
        self::assertSame('sku-001', $output['id']);
        self::assertSame('parent-sku', $output['item_group_id']);
    }

    public function testTestFeedRejectsInvalidConditionAndLogsWhy(): void
    {
        $this->feedMock->method('getData')->willReturnCallback(static fn($key) => $key === 'type' ? 'microsoft_merchant_center' : null);
        $this->fileDriverMock->expects(self::never())->method('fileWrite');
        $this->loggerMock->expects(self::once())->method('warning')->with(self::stringContains('condition'));
        $generator = (new ObjectManager($this))->getObject(Generator::class, [
            'feed' => $this->feedMock, 'fileDriver' => $this->fileDriverMock, 'logger' => $this->loggerMock,
            'microsoftMerchantCenterValidator' => new MicrosoftMerchantCenter(), 'testSku' => 'sku-001',
        ]);
        (new \ReflectionMethod($generator, 'writeFeed'))->invoke(
            $generator, array_replace(self::validRow(), ['condition' => 'damaged'])
        );
        self::assertSame(1, (new \ReflectionProperty($generator, 'countProductsSkipped'))->getValue($generator));
        self::assertSame(0, (new \ReflectionProperty($generator, 'countProductsExported'))->getValue($generator));
    }

    public function testStockFilteringRunsBeforeMicrosoftFormatting(): void
    {
        $meta = $this->feedTypes()['microsoft_merchant_center'];
        $directive = $meta['directives']['directive_availability'];
        $this->feedMock->method('getData')->willReturnCallback(static fn($key) => $key === 'type' ? 'microsoft_merchant_center' : null);
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
