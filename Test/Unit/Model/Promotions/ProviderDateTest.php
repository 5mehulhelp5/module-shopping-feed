<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Promotions;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use MageOS\ShoppingFeed\Model\Promotions\Provider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ProviderDateTest extends TestCase
{
    public function testPromotionDatesFallBackToUtcWhenTheStoreHasNoTimezone(): void
    {
        $provider = $this->provider('');

        $this->assertSame('2026/10/01', $provider->prepareDate('', '2026/10/01'));
        $this->assertSame('2026-10-01T12:00:00+00:00', $provider->prepareDate('', '2026/10/01', false, 'c'));
    }

    public function testPromotionDatesUseTheStoreTimezone(): void
    {
        $provider = $this->provider('America/New_York');

        $this->assertSame('2026-10-01T12:00:00-04:00', $provider->prepareDate('', '2026/10/01', false, 'c'));
    }

    private function provider(string $configuredTimezone): Provider
    {
        $timezone = $this->createMock(Timezone::class);
        $timezone->method('getConfigTimezone')->willReturn($configuredTimezone);
        $timezone->method('date')->willReturn(new \DateTime('2026-09-15 08:00:00', new \DateTimeZone('UTC')));
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->method('create')->willReturn($timezone);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($this->createMock(Store::class));
        $feed = new DataObject(['store_id' => 1]);

        $provider = $this->getMockBuilder(Provider::class)->disableOriginalConstructor()
            ->onlyMethods(['getFeed'])->getMock();
        $provider->method('getFeed')->willReturn($feed);
        (new \ReflectionProperty(Provider::class, 'objectManager'))->setValue($provider, $objectManager);
        (new \ReflectionProperty(Provider::class, 'storeManager'))->setValue($provider, $storeManager);

        return $provider;
    }
}
