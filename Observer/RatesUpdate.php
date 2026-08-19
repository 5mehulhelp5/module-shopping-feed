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

class RatesUpdate implements ObserverInterface
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Shipping
     */
    protected $shipping;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Shipping $shipping
     */
    public function __construct(
        \MageOS\ShoppingFeed\Model\Shipping $shipping,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->shipping = $shipping;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Clean shipping cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->scopeConfig->getValue(\Magento\Directory\Model\Observer::IMPORT_ENABLE)) {
            $collection = $this->shipping->getCollection();
            foreach ($collection as $item) {
                $item->delete();
            }
        }
    }
}
