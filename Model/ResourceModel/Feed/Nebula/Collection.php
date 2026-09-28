<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Model\ResourceModel\Feed\Nebula;

/** Separate read model so the grid never serializes feed configuration or credentials. */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        private \MageOS\ShoppingFeed\Model\Adminhtml\FeedRow $row,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _construct()
    {
        $this->_init(\MageOS\ShoppingFeed\Model\Feed::class, \MageOS\ShoppingFeed\Model\ResourceModel\Feed::class);
    }

    public function toArray($arrRequiredFields = [])
    {
        $items = [];
        foreach ($this->getItems() as $feed) {
            $items[] = $this->row->format($feed);
        }
        return ['totalRecords' => $this->getSize(), 'items' => $items];
    }
}
