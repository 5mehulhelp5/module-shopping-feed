<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Integration;

use Magento\Framework\DataObject;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;
use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\FeedFactory;
use MageOS\ShoppingFeed\Model\Generator\Queue;
use MageOS\ShoppingFeed\Model\Generator\QueueFactory;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class DatabaseContractTest extends TestCase
{
    public function testFeedAndManualQueueEntryPersistWithRequiredDefaults(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Feed $feed */
        $feed = $objectManager->get(FeedFactory::class)->create();
        $feed->setData([
            'name' => 'Shopping Feed Integration Test',
            'store_id' => 1,
            'type' => 'generic',
            'config' => new DataObject([
                'general_feed_dir' => 'pub/media/mageos-shopping-feed/integration-test',
                'file_feed' => 'feed_%s.txt',
                'file_log' => 'feed_%s.log',
                'columns_product_columns' => [],
                'shipping_cache_enabled' => 0,
            ]),
        ]);
        $feed->save();

        $this->assertNotEmpty($feed->getId());
        $this->assertSame([], $feed->getMessages());

        /** @var Queue $queue */
        $queue = $objectManager->get(QueueFactory::class)->create();
        $queue->add($feed);

        $connection = $objectManager->get(ResourceConnection::class)->getConnection();
        $row = $connection->fetchRow(
            $connection->select()
                ->from($connection->getTableName('mageos_shopping_feed_feed_queue'))
                ->where('id = ?', (int) $queue->getId())
        );

        $this->assertSame((int) $feed->getId(), (int) $row['feed_id']);
        $this->assertNull($row['schedule_id']);
        $this->assertSame(0, (int) $row['is_read']);
        $this->assertSame('[]', $row['message']);
    }
    public function testQueueLookupDoesNotRetainAnotherFeedsItemsOrFilters(): void
    {
        $om = Bootstrap::getObjectManager();
        $feeds = [];
        foreach (['A', 'B'] as $name) {
            $feed = $om->create(Feed::class)->setName('Queue regression ' . $name)->setType('generic')->setStoreId(1);
            $feed->getConfig()->setData('shipping_cache_enabled', 0);
            $feed->save();
            $feeds[] = $feed;
        }
        $lookup = $om->create(\MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue\Collection::class);
        $this->assertNull($lookup->getQueue((int)$feeds[0]->getId())->getId());
        foreach ($feeds as $feed) {
            $om->create(Queue::class)->add($feed);
        }
        foreach ([$feeds[0], $feeds[1], $feeds[0]] as $feed) {
            $this->assertSame((int)$feed->getId(), (int)$lookup->getQueue((int)$feed->getId())->getFeedId());
        }
        $this->assertNotNull($lookup->getQueue()->getId());
    }

    public function testTextAndStructuredConfigSurviveRepeatedDatabaseSaves(): void
    {
        $om = Bootstrap::getObjectManager();
        $feed = $om->create(Feed::class)->setName('Config regression')->setType('generic')->setStoreId(1);
        $feed->getConfig()->setData('shipping_cache_enabled', 0);
        $feed->save();
        foreach (['[plain text default]', '[1,2]', '{"key":"value"}', '"quoted"'] as $value) {
            $feed->getConfig()->setData('output_params_default_value', $value);
            $feed->getConfig()->setData('shipping_carrier_realtime', ['ups', 'usps']);
            $feed->setHasDataChanges(true);
            $feed->save();
            $feed = $om->create(Feed::class)->load($feed->getId());
            $this->assertSame($value, $feed->getConfig('output_params_default_value'));
            $this->assertSame(['ups', 'usps'], $feed->getConfig('shipping_carrier_realtime'));
        }
    }

}
