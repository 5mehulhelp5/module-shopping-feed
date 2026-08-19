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

namespace MageOS\ShoppingFeed\Model\ResourceModel\Shipping;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'MageOS\ShoppingFeed\Model\Shipping',
            'MageOS\ShoppingFeed\Model\ResourceModel\Shipping'
        );
    }

    /**
     * @param  \Magento\Store\Model\Store $store
     * @return $this
     */
    public function filterByStore(\Magento\Store\Model\Store $store)
    {
        $this->addFieldToFilter('store_id', $store->getStoreId());
        return $this;
    }

    /**
     * @param  \MageOS\ShoppingFeed\Model\Feed $feed
     * @return $this
     */
    public function filterByFeed(\MageOS\ShoppingFeed\Model\Feed $feed)
    {
        $this->addFieldToFilter('feed_id', $feed->getId());
        return $this;
    }

    /**
     * @param  $currencyCode
     * @return $this
     */
    public function filterByCurrencyCode($currencyCode)
    {
        $this->addFieldToFilter('currency_code', $currencyCode);
        return $this;
    }

    public function filterByProduct($product)
    {
        $this->addFieldToFilter('product_id', $product->getId());
        return $this;
    }

    public function filterByDate($date)
    {
        $this->addFieldToFilter('updated_at', ['gt' => $date]);
        return $this;
    }
}
