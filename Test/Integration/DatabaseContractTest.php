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
}
