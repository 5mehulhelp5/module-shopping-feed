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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Configurable;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class LocalInventoryFilterFields implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $feed = $observer->getEvent()->getFeed();
        $form = $observer->getEvent()->getForm();

        if ($form->getElement('feed_configurable') && $feed->getType() == 'google_local_inventory') {
            $fieldset = $form->getElement('feed_configurable');
            $fieldset->removeField('config_configurable_associated_products_link_add_unique');
            $fieldset->removeField('config_configurable_attribute_merge_value_separator');
        }
    }
}
