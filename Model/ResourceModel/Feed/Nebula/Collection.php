<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Model\ResourceModel\Feed\Nebula;

/** Separate read model so the grid never serializes feed configuration or credentials. */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \MageOS\ShoppingFeed\Model\Adminhtml\FeedRow $row
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
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

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\MageOS\ShoppingFeed\Model\Feed::class, \MageOS\ShoppingFeed\Model\ResourceModel\Feed::class);
    }

    /**
     * Grid rows formatted by FeedRow, with the total record count.
     *
     * @param array $arrRequiredFields ignored; rows always use the grid columns
     * @return array
     */
    public function toArray($arrRequiredFields = [])
    {
        $items = [];
        foreach ($this->getItems() as $feed) {
            $items[] = $this->row->format($feed);
        }
        return ['totalRecords' => $this->getSize(), 'items' => $items];
    }
}
