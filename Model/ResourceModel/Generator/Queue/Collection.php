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
        $this->_init('MageOS\ShoppingFeed\Model\Generator\Queue', 'MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue');
    }

    /**
     * @param  $feedId int
     * @return \MageOS\ShoppingFeed\Model\Generator\Queue
     */
    public function getQueue($feedId = 0)
    {
        $this->clean();

        $this->setOrder('is_read', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->setOrder('created_at', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->setOrder('schedule_id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC); // We proccess manual requests first!

        if ($feedId > 0) {
            $this->getSelect()
                ->where('feed_id = ?', $feedId);
        }
        $this->getSelect()
            ->where('is_read = 0 OR (is_read = 1 && TO_DAYS(`created_at`) < TO_DAYS(?))', $this->date->date())
            ->limit(1);
        $this->setPageSize(1);

        return $this->getFirstItem();
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

    public function clean()
    {
        $this->getConnection()->query(
            'DELETE q FROM '. $this->getMainTable(). ' q
            LEFT JOIN '. $this->getTable('mageos_shopping_feed_feed'). ' f ON f.id = q.feed_id
            WHERE f.id IS NULL'
        );

        return $this;
    }
}
