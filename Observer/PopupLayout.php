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
namespace MageOS\ShoppingFeed\Observer;

use Magento\Framework\Event\ObserverInterface;

class PopupLayout implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $action = $observer->getEvent()->getFullActionName();
        if (in_array($action, ['mageos_shopping_feed_feed_test', 'mageos_shopping_feed_feed_viewlog'])) {
            $observer->getLayout()
                ->unsetElement('global.search')
                ->unsetElement('user')
                ->unsetElement('logo')
                ->unsetElement('menu')
                ->unsetElement('breadcrumbs')
                ->unsetElement('notification.messages')
                ->unsetElement('copyright')
                ->unsetElement('version')
                ->unsetElement('logger');
        }
    }
}
