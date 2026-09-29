<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\FeedTypes;

use MageOS\ShoppingFeed\Model\Feed\Validation\OpenAiGoogleCompatible;
use MageOS\ShoppingFeed\Model\FeedTypes\Config\Converter;
use MageOS\ShoppingFeed\Model\Generator;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class OpenAiGoogleCompatibleTest extends ModelFramework
{
    private function feedTypes(): array
    {
        $document = new \DOMDocument();
        $document->load(dirname(__DIR__, 4) . '/etc/mageos_shopping_feed.xml');
        $decoder = $this->createMock(DecoderInterface::class);
        $decoder->method('decode')->willReturnCallback(static fn($value) => json_decode($value, true));
        return (new Converter($decoder))->convert($document)['feed'];
    }

    public function testPresetUsesOnlyCompatibilityFieldsAndExistingVariantMappers(): void
    {
        $types = $this->feedTypes();
        self::assertArrayHasKey('openai_google_compatible', $types);
        $preset = $types['openai_google_compatible'];
        $defaults = $preset['default_feed_config'];
        $columns = $defaults['columns']['product_columns'];
        foreach (['id', 'title', 'description', 'link', 'image_link', 'price', 'availability', 'brand',
            'gtin', 'mpn', 'identifier_exists', 'availability_date', 'expiration_date', 'item_group_id'] as $column) {
            self::assertArrayHasKey($column, $columns);
        }
        foreach (['item_id', 'seller_name', 'is_eligible_search', 'enable_checkout', 'item_group_title', 'variant_option'] as $column) {
            self::assertArrayNotHasKey($column, $columns);
        }
        self::assertStringContainsString('beta', $preset['label']);
        self::assertSame('sku', $columns['item_group_id']['param']);
        self::assertEmpty($columns['mpn']['param']);
        self::assertEmpty($columns['gtin']['param']);
        self::assertEmpty($columns['availability_date']['param']);
        self::assertEmpty($columns['identifier_exists']['param']);
        self::assertSame('directive_static_value', $columns['identifier_exists']['attribute']);
        self::assertSame('2', $columns['expiration_date']['param']);
        self::assertSame('\t', $defaults['output_params']['delimiter']);
        self::assertSame('UTF-8', $defaults['output_params']['encoding']);
        self::assertStringEndsWith('.tsv', $defaults['file']['feed']);
        self::assertSame('1', $defaults['configurable']['associated_products_mode']);
        self::assertSame('1', $defaults['general']['complex_duplicates_check']);
        self::assertSame('5000', $defaults['filters']['filters_output_limit'][0]['limit']);
        self::assertSame('150', $defaults['filters']['filters_output_limit'][1]['limit']);
        self::assertSame(
            $types['google_shopping']['directives']['directive_availability'],
            $preset['directives']['directive_availability']
        );
    }

    public static function validRow(): array
    {
        return [
            'id' => 'sku-001', 'title' => 'Café "Blue", Shirt', 'description' => 'Cotton shirt',
            'availability' => 'in_stock', 'condition' => 'new', 'price' => '29.99 USD',
            'link' => 'https://example.com/shirt', 'image_link' => 'https://example.com/shirt.jpg',
            'brand' => 'Example', 'gtin' => '09506000134352', 'mpn' => '', 'item_group_id' => 'parent-sku',
        ];
    }

    /** @dataProvider validRows */
    #[DataProvider('validRows')]
    public function testAcceptedCompatibilityRows(array $changes): void
    {
        self::assertSame([], (new OpenAiGoogleCompatible())->validate(array_replace(self::validRow(), $changes))['errors']);
    }

    public static function validRows(): array
    {
        return [
            [[]],
            [['gtin' => '', 'mpn' => 'REAL-MPN']],
            [['gtin' => '0 9506000-134352']],
            [['gtin' => '', 'identifier_exists' => 'false']],
            [['gtin' => '', 'identifier_exists' => 'no']],
            [['gtin' => '', 'identifier_exists' => false]],
            [['availability' => 'out_of_stock']],
            [['availability' => 'preorder', 'availability_date' => '2026-10-15']],
            [['availability' => 'backorder', 'availability_date' => '2026-10-15T09:00Z']],
            [['expiration_date' => '2026-10-01T00:00:00-0400']],
            [['sale_price' => '19.99 USD']],
            [['sale_price' => '19.99 USD', 'sale_price_effective_date' => '2026-10-01/2026-10-01']],
            [['sale_price' => '19.99 USD', 'sale_price_effective_date' => '2026-10-01T12:00Z/2026-10-01T23:59:59+00:00']],
            [['price' => '0.00 USD', 'google_product_category' => '267', 'subscription_cost' => 'month:12:30.00 USD']],
            [['price' => '0.00 USD', 'google_product_category' => '4745', 'subscription_cost' => 'year:1:100.00 USD']],
        ];
    }

    /** @dataProvider invalidRows */
    #[DataProvider('invalidRows')]
    public function testInvalidCompatibilityRowsAreRejected(array $changes, string $field): void
    {
        $result = (new OpenAiGoogleCompatible())->validate(array_replace(self::validRow(), $changes));
        self::assertNotEmpty($result['errors']);
        self::assertStringContainsString($field, implode(' ', $result['errors']));
    }

    public static function invalidRows(): array
    {
        $cases = [];
        foreach (['id', 'title', 'description', 'availability', 'price', 'link', 'image_link', 'brand'] as $field) {
            $cases['missing ' . $field] = [[$field => ''], $field];
        }
        foreach (['title' => str_repeat('é', 151), 'description' => str_repeat('é', 5001),
            'id' => "bad\x00id", 'condition' => 'damaged', 'brand' => 'unknown',
            'availability' => 'pre_order', 'price' => '29.999 USD',
            'link' => 'https://user:pass@example.com/shirt', 'image_link' => '/image.jpg',
            'additional_image_link' => 'https://u:p@example.com/a.jpg', 'identifier_exists' => 'maybe',
            'expiration_date' => '2026-10-01', 'availability_date' => '2026-02-30',
            'gender' => 'other', 'age_group' => 'teen', 'size_system' => 'USA',
            'item_group_id' => 'sku-001'] as $field => $value) {
            $cases['invalid ' . $field] = [[$field => $value], $field];
        }
        $cases['HTML title'] = [['title' => '<b>Shirt</b>'], 'title'];
        $cases['array price'] = [['price' => ['29.99 USD']], 'price'];
        $cases['missing identifiers'] = [['gtin' => ''], 'gtin'];
        $cases['bad GTIN checksum'] = [['gtin' => '09506000134353'], 'gtin'];
        $cases['restricted GTIN'] = [['gtin' => '0201234567899'], 'gtin'];
        $cases['missing preorder date'] = [['availability' => 'preorder'], 'availability_date'];
        $cases['missing backorder date'] = [['availability' => 'backorder'], 'availability_date'];
        $cases['date without timezone'] = [['availability' => 'preorder', 'availability_date' => '2026-10-15T09:00'], 'availability_date'];
        $cases['equal sale'] = [['sale_price' => '29.99 USD'], 'sale_price'];
        $cases['higher sale'] = [['sale_price' => '30.00 USD'], 'sale_price'];
        $cases['zero sale'] = [['sale_price' => '0.00 USD'], 'sale_price'];
        $cases['other sale currency'] = [['sale_price' => '19.99 EUR'], 'sale_price'];
        $cases['date without sale'] = [['sale_price_effective_date' => '2026-10-01/2026-10-02'], 'sale_price'];
        $cases['reversed dates'] = [['sale_price' => '19.99 USD', 'sale_price_effective_date' => '2026-10-02/2026-10-01'], 'sale_price_effective_date'];
        $cases['invalid dates'] = [['sale_price' => '19.99 USD', 'sale_price_effective_date' => '2026-02-30/2026-03-01'], 'sale_price_effective_date'];
        $cases['zero price'] = [['price' => '0.00 USD'], 'subscription_cost'];
        $cases['zero nonmobile'] = [['price' => '0.00 USD', 'google_product_category' => '999', 'subscription_cost' => 'month:12:30.00 USD'], 'google_product_category'];
        $cases['zero subscription periods'] = [['price' => '0.00 USD', 'google_product_category' => '267', 'subscription_cost' => 'month:0:30.00 USD'], 'subscription_cost'];
        $cases['subscription currency'] = [['price' => '0.00 USD', 'google_product_category' => '267', 'subscription_cost' => 'month:12:30.00 EUR'], 'subscription_cost'];
        return $cases;
    }

    public function testZeroPriceSurvivesOnlyTheOpenAiPriceFormatter(): void
    {
        $adapter = $this->getMockBuilder(\MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple::class)
            ->disableOriginalConstructor()->onlyMethods(['getFilter'])->getMock();
        $adapter->setData('store_currency_code', 'USD');
        $formatter = (new ObjectManager($this))->getObject(\MageOS\ShoppingFeed\Model\Product\Formatter\OpenAi\Price::class);
        $formatter->setAdapter($adapter);
        self::assertSame('0.00 USD', $formatter->run(0));
        self::assertSame('29.99 USD', $formatter->run(29.99));
        $generic = (new ObjectManager($this))->getObject(\MageOS\ShoppingFeed\Model\Product\Formatter\Price\Currency::class);
        $generic->setAdapter($adapter);
        self::assertSame('', $generic->run(0));
    }

    public function testConfigurableRequiresGroupAndNonVariantDoesNot(): void
    {
        $row = self::validRow();
        unset($row['item_group_id']);
        $validator = new OpenAiGoogleCompatible();
        self::assertSame([], $validator->validate($row)['errors']);
        self::assertStringContainsString('item_group_id', implode(' ', $validator->validate($row, true)['errors']));
    }

    public function testGeneratorWritesQuotedTsvAndKeepsIdHeader(): void
    {
        $types = $this->feedTypes();
        $defaults = $types['openai_google_compatible']['default_feed_config'];
        $columns = array_values($defaults['columns']['product_columns']);
        usort($columns, static fn($left, $right) => $left['order'] <=> $right['order']);
        $settings = [];
        foreach ($defaults as $group => $values) {
            foreach ($values as $key => $value) {
                $settings[$group . '_' . $key] = $value;
            }
        }
        $this->feedMock->method('getData')->willReturnCallback(static fn($key) => $key === 'type' ? 'openai_google_compatible' : null);
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
            'openAiGoogleCompatibleValidator' => new OpenAiGoogleCompatible(),
        ]);
        $generator->setData('temporary_handle', 'handle');
        $write = new \ReflectionMethod($generator, 'writeFeed');
        $headers = array_column($columns, 'column');
        $write->invoke($generator, array_combine($headers, $headers), false);
        $row = array_replace(self::validRow(), ['gtin' => '', 'identifier_exists' => false]) + ['additional_image_link' => 'https://example.com/a.jpg,https://example.com/b.jpg', 'sale_price' => '19.99 USD', 'sale_price_effective_date' => '2026-09-01/2026-10-31'];
        $write->invoke($generator, $row);
        $lines = explode(PHP_EOL, $written);
        self::assertCount(2, $lines);
        self::assertSame($headers, str_getcsv($lines[0], "\t", '"', ''));
        $values = str_getcsv($lines[1], "\t", '"', '');
        self::assertCount(count($headers), $values);
        $output = array_combine($headers, $values);
        self::assertSame($row['title'], $output['title']);
        self::assertSame($row['additional_image_link'], $output['additional_image_link']);
        self::assertSame('in_stock', $output['availability']);
        self::assertSame('19.99 USD', $output['sale_price']);
        self::assertSame('2026-09-01/2026-10-31', $output['sale_price_effective_date']);
        self::assertSame('false', $output['identifier_exists']);
        self::assertSame('sku-001', $output['id']);
        self::assertSame('parent-sku', $output['item_group_id']);
    }

    public function testTestFeedRejectsInvalidConditionAndLogsWhy(): void
    {
        $this->feedMock->method('getData')->willReturnCallback(static fn($key) => $key === 'type' ? 'openai_google_compatible' : null);
        $this->fileDriverMock->expects(self::never())->method('fileWrite');
        $this->loggerMock->expects(self::once())->method('warning')->with(self::stringContains('condition'));
        $generator = (new ObjectManager($this))->getObject(Generator::class, [
            'feed' => $this->feedMock, 'fileDriver' => $this->fileDriverMock, 'logger' => $this->loggerMock,
            'openAiGoogleCompatibleValidator' => new OpenAiGoogleCompatible(), 'testSku' => 'sku-001',
        ]);
        (new \ReflectionMethod($generator, 'writeFeed'))->invoke(
            $generator, array_replace(self::validRow(), ['condition' => 'damaged'])
        );
        self::assertSame(1, (new \ReflectionProperty($generator, 'countProductsSkipped'))->getValue($generator));
        self::assertSame(0, (new \ReflectionProperty($generator, 'countProductsExported'))->getValue($generator));
    }

    public function testGeneratorRequiresGroupingForConfigurableOutput(): void
    {
        $this->feedMock->method('getData')->willReturnCallback(static fn($key) => $key === 'type' ? 'openai_google_compatible' : null);
        $this->loggerMock->expects(self::once())->method('warning')->with(self::stringContains('item_group_id'));
        $this->fileDriverMock->expects(self::never())->method('fileWrite');
        $generator = (new ObjectManager($this))->getObject(Generator::class, [
            'feed' => $this->feedMock, 'fileDriver' => $this->fileDriverMock, 'logger' => $this->loggerMock,
            'openAiGoogleCompatibleValidator' => new OpenAiGoogleCompatible(), 'testSku' => 'parent-sku',
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

}
