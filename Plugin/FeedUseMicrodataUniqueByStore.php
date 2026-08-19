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
 * @copyright Copyright (c) 2023 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */

namespace MageOS\ShoppingFeed\Plugin;


class FeedUseMicrodataUniqueByStore
{
    /**
     * @var \MageOS\ShoppingFeed\Model\ResourceModel\Feed\CollectionFactory
     */
    protected $feedCollectionFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * FeedUseForMicrodataUniqueByStore constructor.
     * @param \MageOS\ShoppingFeed\Model\ResourceModel\Feed\CollectionFactory $feedCollectionFactory
     */
    public function __construct(
        \MageOS\ShoppingFeed\Model\ResourceModel\Feed\CollectionFactory $feedCollectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \MageOS\ShoppingFeed\Model\Feed
     */
    public function beforeSave(\MageOS\ShoppingFeed\Model\Feed $subject)
    {
        if ($subject->getData('use_microdata') == "0") {
            return;
        }

        $feedLookup = $this->feedCollectionFactory->create()
            ->addFieldToFilter('store_id', $subject->getStore()->getId())
            ->addFieldToFilter('use_microdata', 1)
            ->addFieldToFilter('id', ['neq' => $subject->getId()]);
        $count = $feedLookup->count();

        if ($count) {
            $subject->setData('use_microdata', 0);
            $this->messageManager->addWarningMessage(__('Only one feed per store may be used for microdata. ("Use for microdata" = Yes)'));
            $this->messageManager->addSuccessMessage(__('To set microdata for "{store}" to use this feed\'s mapping,
            edit the other feeds and set "Use for microdata" = No', [ 'store' => $subject->getStore()->getName()]));
        }
    }
}
