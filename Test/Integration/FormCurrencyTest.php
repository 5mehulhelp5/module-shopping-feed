<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Integration;

use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use MageOS\ShoppingFeed\Model\Adminhtml\FeedFormData;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Feed\Converter;
use MageOS\ShoppingFeed\Ui\DataProvider\Feed\FormDataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class FormCurrencyTest extends TestCase
{
    /**
     * @magentoConfigFixture current_store currency/options/default EUR
     * @magentoConfigFixture current_store currency/options/allow USD,EUR
     */
    public function testUnchangedSavePreservesAnExistingFeedsEffectiveCurrency(): void
    {
        $om = Bootstrap::getObjectManager();
        $store = $om->get(StoreManagerInterface::class)->getStore();
        $originalCurrency = $store->getCurrentCurrencyCode();
        $registry = $om->get(Registry::class);
        try {
            $store->setCurrentCurrencyCode('USD');
            $feed = $om->create(Feed::class)->setName('Currency regression')->setType('generic')
                ->setStoreId($store->getId())->setStatus(0)->setSchedules([])->setUploads([]);
            $feed->getConfig()->setData('general_currency', '');
            $feed->getConfig()->setData('shipping_cache_enabled', 0);
            $feed->save();
            $feed = $om->create(Feed::class)->load($feed->getId());
            $this->assertSame('EUR', $store->getDefaultCurrencyCode());
            $this->assertSame('USD', $feed->getStore()->getCurrentCurrencyCode());
            $registry->register('feed', $feed);
            $provider = $om->create(FormDataProvider::class, [
                'name' => 'currency_regression', 'primaryFieldName' => 'id', 'requestFieldName' => 'id'
            ]);
            $data = $provider->getData()[$feed->getId()];
            $this->assertSame('USD', $data['config']['general_currency']);
            $formData = $om->get(FeedFormData::class);
            // Saving starts a separate request, whose builder registers the feed again.
            $registry->unregister('feed');
            $om->get(Converter::class)->populateFeedData(
                $formData->decode($formData->encodeForProvider($data))
            )->save();
            $saved = $om->create(Feed::class)->load($feed->getId());
            $this->assertSame('USD', $saved->getConfig('general_currency'));
            $this->assertSame('USD', $saved->getStore()->getCurrentCurrencyCode());
        } finally {
            $registry->unregister('feed');
            $store->setCurrentCurrencyCode($originalCurrency);
        }
    }
}
