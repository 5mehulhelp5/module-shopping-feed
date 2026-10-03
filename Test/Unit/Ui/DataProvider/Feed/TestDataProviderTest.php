<?php
declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Ui\DataProvider\Feed;

use MageOS\ShoppingFeed\Ui\DataProvider\Feed\TestDataProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class TestDataProviderTest extends TestCase
{
    /** @dataProvider lookupValues */
    #[DataProvider('lookupValues')]
    public function testMalformedLookupInputCanBeRedisplayedWithoutWarnings($value, string $expected): void
    {
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $request->method('getParam')->willReturnCallback(static fn($key) => $key === 'sku' ? $value : ['invalid']);
        $registry = $this->createMock(\Magento\Framework\Registry::class);
        $registry->method('registry')->with('feed')->willReturn(new \Magento\Framework\DataObject(['id' => 17]));
        $provider = (new \ReflectionClass(TestDataProvider::class))->newInstanceWithoutConstructor();
        foreach (['request' => $request, 'registry' => $registry] as $name => $dependency) {
            (new \ReflectionProperty(TestDataProvider::class, $name))->setValue($provider, $dependency);
        }
        self::assertSame([17 => ['id' => 17, 'sku' => $expected, 'type' => 'sku']], $provider->getData());
    }

    public static function lookupValues(): array
    {
        return [[['bad'], ''], [false, ''], [null, ''], ['0', '0'], ['00042', '00042'], [42, '42']];
    }
}
