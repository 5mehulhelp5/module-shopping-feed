<?php
/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   MageOS_ShoppingFeed
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */


namespace MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method \MageOS\ShoppingFeed\Model\Generator\Queue getFirstItem()
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {

        $this->date = $date;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \MageOS\ShoppingFeed\Model\Generator\Queue::class,
            \MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue::class
        );
    }

    /**
     * Use an independent query so repeated lookups retain neither items nor criteria.
     *
     * @param  int $feedId
     * @return \MageOS\ShoppingFeed\Model\Generator\Queue
     */
    public function getQueue($feedId = 0)
    {
        $this->clean();

        $lookup = clone $this;
        $lookup->clear();

        // We proccess manual requests first!
        $lookup->setOrder('is_read', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->setOrder('created_at', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->setOrder('schedule_id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        if ($feedId > 0) {
            $lookup->getSelect()
                ->where('feed_id = ?', $feedId);
        }
        // Running rows also reserve their feed for scheduling. The process lock
        // distinguishes a live worker from an interrupted run, regardless of date.
        $lookup->getSelect()->limit(1);
        $lookup->setPageSize(1);

        return $lookup->getFirstItem();
    }

    /**
     * Used to emulate after load functionality for each item without loading them
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->walk('afterLoad');

        return $this;
    }

    /**
     * Delete queue rows whose feed no longer exists.
     *
     * @return $this
     */
    public function clean()
    {
        // Remove queue rows whose feed no longer exists.
        $connection = $this->getConnection();
        $orphans = $connection->select()
            ->from(['q' => $this->getMainTable()], [])
            ->joinLeft(['f' => $this->getTable('mageos_shopping_feed_feed')], 'f.id = q.feed_id', [])
            ->where('f.id IS NULL');
        $connection->query($connection->deleteFromSelect($orphans, 'q'));

        return $this;
    }
}
