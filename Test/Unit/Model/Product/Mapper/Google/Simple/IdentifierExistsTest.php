<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Google\Simple;

use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Logger;
use MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract;
use MageOS\ShoppingFeed\Model\Product\Filter;
use MageOS\ShoppingFeed\Model\Product\Mapper\Google\Simple\IdentifierExists;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class IdentifierExistsTest extends TestCase
{
    /** @dataProvider identifierCases */
    #[DataProvider('identifierCases')]
    public function testAbsenceRequiresExplicitConfirmation(array $values, string $mode, string $expected): void
    {
        $feed = $this->createMock(Feed::class);
        $feed->method('getColumnsMap')->willReturn(array_map(fn($name) => ['column' => $name], array_keys($values)));
        $adapter = $this->createMock(AdapterAbstract::class);
        $adapter->method('getFeed')->willReturn($feed);
        $adapter->method('getFilter')->willReturn($this->createMock(Filter::class));
        $adapter->method('getMapValue')->willReturnCallback(fn($map) => $values[$map['column']]);
        $mapper = new IdentifierExists($this->createMock(Logger::class));
        $mapper->addAdapter($adapter);
        $this->assertSame($expected, $mapper->map(['column' => 'identifier_exists', 'param' => $mode]));
    }

    public static function identifierCases(): array
    {
        return [
            'GTIN without brand' => [['gtin' => '0614141123452', 'brand' => ''], '', ''],
            'MPN without brand' => [['mpn' => 'ABC-123'], 'brand,gtin,mpn', ''],
            'unmapped is unknown' => [[], '', ''],
            'legacy list does not infer absence' => [['brand' => '', 'gtin' => ''], 'brand,gtin', ''],
            'confirmed absent' => [[], 'no_identifiers', 'FALSE'],
            'confirmed absent conflicts with GTIN' => [['gtin' => '0614141123452'], 'no_identifiers', ''],
        ];
    }
}
