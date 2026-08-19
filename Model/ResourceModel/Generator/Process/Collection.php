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


namespace MageOS\ShoppingFeed\Model\ResourceModel\Generator\Process;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageOS\ShoppingFeed\Model\Generator\Process', 'MageOS\ShoppingFeed\Model\ResourceModel\Generator\Process');
    }

    public function setFeedFilter(\MageOS\ShoppingFeed\Model\Feed $feed)
    {
        if (!$this->hasFlag('feed_filter')) {
            $this->addFieldToFilter('feed_id', ['eq' => $feed->getId()]);
            $this->setFlag('feed_filter');
        }
        return $this;
    }

    public function setProductFilter(\Magento\Catalog\Model\Product $product)
    {
        if (!$this->hasFlag('product_filter')) {
            $this->addFieldToFilter('item_id', ['eq' => $product->getId()]);
            $this->setFlag('product_filter');
        }
        return $this;
    }

    public function truncate($feed = null)
    {
        if (!is_null($feed)) {
            $this->setFeedFilter($feed);
        }

        foreach ($this->getItems() as $key => $item) {
            $item->delete();
            unset($this->_items[$key]);
        }

        return $this;
    }
}
