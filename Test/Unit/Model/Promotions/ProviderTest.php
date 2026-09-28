<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Promotions;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Store\Model\StoreManagerInterface;
use MageOS\ShoppingFeed\Model\Feed\OutputPath;
use MageOS\ShoppingFeed\Model\Promotions\Provider;
use MageOS\ShoppingFeed\Model\Promotions\Provider\Collection;
use MageOS\ShoppingFeed\Model\Promotions\Provider\Map;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ProviderTest extends TestCase
{
    public function testPromotionCacheIsReloadedWhenConfigurationHashChanges(): void
    {
        $directoryList = $this->createMock(DirectoryList::class);
        $directoryList->method('getPath')->with('var')->willReturn('/magento/var');

        $helper = $this->createMock(JsonHelper::class);
        $helper->method('jsonDecode')->willReturnCallback(
            static fn (string $value): array => json_decode($value, true)
        );

        $fileDriver = $this->createMock(File::class);
        $fileDriver->method('isExists')->willReturn(true);
        $fileDriver->method('isReadable')->willReturn(true);
        $fileDriver->expects($this->exactly(2))->method('fileGetContents')->willReturnOnConsecutiveCalls(
            '{"hash":"first","cache":{"42":["FIRST"]}}',
            '{"hash":"second","cache":{"42":["SECOND"]}}'
        );

        $provider = $this->createProvider($helper, $directoryList, $fileDriver);
        $method = new \ReflectionMethod($provider, 'getHashCache');

        $this->assertSame(['42' => ['FIRST']], $method->invoke($provider, 'first'));
        $this->assertSame(['42' => ['SECOND']], $method->invoke($provider, 'second'));
    }

    public function testPromotionCacheIsReplacedAtomically(): void
    {
        $cacheFile = '/magento/var/cache/mageos_shopping_feed_promotions.cache';
        $temporaryFile = $cacheFile . '.' . getmypid() . '.tmp';

        $directoryList = $this->createMock(DirectoryList::class);
        $directoryList->method('getPath')->with('var')->willReturn('/magento/var');

        $helper = $this->createMock(JsonHelper::class);
        $helper->expects($this->once())->method('jsonEncode')->willReturn('{"cache":[],"hash":"hash"}');

        $fileDriver = $this->createMock(File::class);
        $fileDriver->method('isExists')->willReturn(false);
        $fileDriver->expects($this->once())->method('createDirectory')->with('/magento/var/cache');
        $fileDriver->expects($this->once())
            ->method('filePutContents')
            ->with($temporaryFile, '{"cache":[],"hash":"hash"}');
        $fileDriver->expects($this->once())->method('rename')->with($temporaryFile, $cacheFile);

        $provider = $this->createProvider($helper, $directoryList, $fileDriver);

        $method = new \ReflectionMethod($provider, 'setPromotionCache');
        $method->invoke($provider, 42, 'hash', ['PROMO']);
    }

    public function testMultipleIncludedRulesAreFilteredAsSeparateIds(): void
    {
        $feed = $this->createMock(\MageOS\ShoppingFeed\Model\Feed::class);
        $feed->method('getConfig')->willReturnMap([
            ['promotions_enabled', null, true],
            ['promotions_provider_widget', null, [
                'hash' => 'test', 'counter' => 1,
                'promotion' => [12 => ['include' => 1], 34 => ['include' => 1]],
            ]],
        ]);
        $rules = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Rule\Quote\Collection::class);
        $rules->expects($this->once())->method('addFieldToFilter')->with('rule_id', ['in' => [12, 34]])->willReturnSelf();
        $rules->method('getIterator')->willReturn(new \ArrayIterator([]));
        $collection = $this->createMock(Collection::class);
        $collection->method('getPromotionRules')->with($feed)->willReturn($rules);
        $provider = $this->getMockBuilder(Provider::class)->disableOriginalConstructor()
            ->onlyMethods(['getPromotionCache', 'setPromotionCache'])->getMock();
        $provider->method('getPromotionCache')->willReturn(false);
        foreach (['promotionsCollection' => $collection, 'map' => $this->createMock(Map::class)] as $name => $value) {
            (new \ReflectionProperty(Provider::class, $name))->setValue($provider, $value);
        }
        $provider->setFeed($feed);
        $this->assertSame([], $provider->getPromotionIds($this->createMock(\Magento\Catalog\Model\Product::class)));
    }

    private function createProvider(JsonHelper $helper, DirectoryList $directoryList, File $fileDriver): Provider
    {
        return new Provider(
            $helper,
            $this->createMock(StoreManagerInterface::class),
            $this->createMock(ItemFactory::class),
            $directoryList,
            $this->createMock(ObjectManagerInterface::class),
            $this->createMock(Map::class),
            $this->createMock(Collection::class),
            $this->createMock(OutputPath::class),
            $fileDriver
        );
    }
}
