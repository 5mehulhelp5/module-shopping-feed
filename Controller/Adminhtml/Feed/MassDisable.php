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

namespace MageOS\ShoppingFeed\Controller\Adminhtml\Feed;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use MageOS\ShoppingFeed\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;
use MageOS\ShoppingFeed\Model\Feed\Source\Status;
use MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue\CollectionFactory as QueueCollectionFactory;

class MassDisable extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'MageOS_ShoppingFeed::save';

    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var FeedCollectionFactoryx
     */
    protected $collectionFactory;

    /**
     * @var QueueCollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        FeedCollectionFactory $collectionFactory,
        QueueCollectionFactory $queueCollectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->queueCollectionFactory = $queueCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Disable selected feeds
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $feedIds = [];

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        foreach ($collection->getItems() as $feed) {
            $feed->setStatus(Status::STATUS_DISABLED);
            $feed->save();
            $feedIds[] = $feed->getId();
        }

        $queueItems = $this->queueCollectionFactory->create()
            ->addFieldToFilter('feed_id', array('in'=> $feedIds))
            ->getItems();
        foreach ($queueItems as $item) {
            $item->delete();
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been disabled.', $collection->getSize()));
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('mageos_shopping_feed/feed/index');
    }
}
