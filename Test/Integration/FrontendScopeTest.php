<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Integration;

use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use MageOS\ShoppingFeed\Block\Product\View\Configurable\Selection;
use MageOS\ShoppingFeed\Block\Product\View\Microdata;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Plugin\MicrodataRemoverPlugin;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class FrontendScopeTest extends TestCase
{
    /**
     * @magentoConfigFixture default/mageos_shopping_feed/google/microdata_enabled 0
     * @magentoConfigFixture current_store mageos_shopping_feed/google/microdata_enabled 1
     * @magentoConfigFixture default/mageos_shopping_feed/google/dynamic_remarketing_enabled 0
     * @magentoConfigFixture current_store mageos_shopping_feed/google/dynamic_remarketing_enabled 1
     * @magentoConfigFixture default/mageos_shopping_feed/google/google_ads_destination_id AW-DEFAULT
     * @magentoConfigFixture current_store mageos_shopping_feed/google/google_ads_destination_id AW-STORE
     */
    public function testStoreCanEnableFrontendFeaturesIndependently(): void
    {
        $this->selectMicrodataFeed();
        $om = Bootstrap::getObjectManager();
        $this->assertTrue($om->create(Microdata::class)->isEnabled());
        $this->assertTrue($om->create(MicrodataRemoverPlugin::class)->isEnabled());
        $selection = $om->create(Selection::class);
        $this->assertTrue($selection->isDynamicRemarketingEnabled());
        $this->assertSame('AW-STORE', $selection->getGoogleAdsDestinationId());
    }

    /**
     * @magentoConfigFixture default/mageos_shopping_feed/google/microdata_enabled 1
     * @magentoConfigFixture current_store mageos_shopping_feed/google/microdata_enabled 0
     * @magentoConfigFixture default/mageos_shopping_feed/google/dynamic_remarketing_enabled 1
     * @magentoConfigFixture current_store mageos_shopping_feed/google/dynamic_remarketing_enabled 0
     * @magentoConfigFixture default/mageos_shopping_feed/google/google_ads_destination_id AW-DEFAULT
     * @magentoConfigFixture current_store mageos_shopping_feed/google/google_ads_destination_id AW-OTHER
     */
    public function testStoreCanDisableFrontendFeaturesIndependently(): void
    {
        $this->selectMicrodataFeed();
        $om = Bootstrap::getObjectManager();
        $this->assertFalse($om->create(Microdata::class)->isEnabled());
        $this->assertFalse($om->create(MicrodataRemoverPlugin::class)->isEnabled());
        $selection = $om->create(Selection::class);
        $this->assertFalse($selection->isDynamicRemarketingEnabled());
        $this->assertSame('AW-OTHER', $selection->getGoogleAdsDestinationId());
    }

    private function selectMicrodataFeed(): void
    {
        $om = Bootstrap::getObjectManager();
        $storeId = (int) $om->get(StoreManagerInterface::class)->getStore()->getId();
        $om->create(Feed::class)->setType('google_shopping')->setStoreId($storeId)
            ->setName('Frontend scope regression')->setStatus(0)->setUseMicrodata(1)
            ->setSchedules([])->setUploads([])->save();
    }
}
