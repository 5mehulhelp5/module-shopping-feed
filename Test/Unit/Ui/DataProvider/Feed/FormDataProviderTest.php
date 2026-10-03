<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Ui\DataProvider\Feed;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use MageOS\ShoppingFeed\Model\Adminhtml\FeedFormData;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Ui\DataProvider\Feed\Form\Metadata;
use MageOS\ShoppingFeed\Ui\DataProvider\Feed\FormDataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class FormDataProviderTest extends TestCase
{
    public function testExistingBlankCurrencyUsesTheCurrencyAlreadyUsedByGeneration(): void
    {
        self::assertSame('USD', $this->currency(17, ''));
    }

    public function testExistingMissingCurrencyUsesTheCurrencyAlreadyUsedByGeneration(): void
    {
        self::assertSame('USD', $this->currency(17, null));
    }

    public function testNewFeedDefaultsToItsStoreCurrency(): void
    {
        self::assertSame('EUR', $this->currency(null, ''));
    }

    public function testExplicitCurrencyIsPreserved(): void
    {
        self::assertSame('GBP', $this->currency(17, 'GBP'));
    }

    private function currency(?int $id, ?string $saved): string
    {
        $store = $this->createMock(Store::class);
        $store->method('getDefaultCurrencyCode')->willReturn('EUR');
        $store->method('getCurrentCurrencyCode')->willReturn('USD');
        $feed = $this->getMockBuilder(Feed::class)->disableOriginalConstructor()
            ->onlyMethods(['getStore'])->getMock();
        $feed->setData(['id' => $id, 'type' => 'generic', 'store_id' => 1]);
        $feed->method('getStore')->willReturn($store);
        $registry = $this->createMock(Registry::class);
        $registry->method('registry')->with('feed')->willReturn($feed);
        $formData = $this->createMock(FeedFormData::class);
        $formData->method('project')->willReturn(['config' => ['general_currency' => $saved]]);
        $formData->method('redact')->willReturnArgument(0);
        $metadata = $this->createMock(Metadata::class);
        $metadata->method('configKeys')->willReturn(['general_currency']);
        $dataPersistor = $this->createMock(DataPersistorInterface::class);
        $pool = $this->createMock(PoolInterface::class);
        $pool->method('getModifiersInstances')->willReturn([]);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);
        $provider = (new \ReflectionClass(FormDataProvider::class))->newInstanceWithoutConstructor();
        $dependencies = compact('registry', 'formData', 'metadata', 'dataPersistor', 'pool', 'storeManager');
        foreach ($dependencies as $name => $value) {
            (new \ReflectionProperty(FormDataProvider::class, $name))->setValue($provider, $value);
        }
        return $provider->getData()[$id ?: '']['config']['general_currency'];
    }
}
