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


namespace MageOS\ShoppingFeed\Model\ResourceModel\Feed\Schedule;

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
            \MageOS\ShoppingFeed\Model\Feed\Schedule::class,
            \MageOS\ShoppingFeed\Model\ResourceModel\Feed\Schedule::class
        );
    }

    /**
     * Add feed filter
     *
     * @param  \MageOS\ShoppingFeed\Model\Feed $feed
     * @return $this
     */
    public function setFeedFilter(\MageOS\ShoppingFeed\Model\Feed $feed)
    {
        $this->addFieldToFilter('feed_id', $feed->getId());

        return $this;
    }

    /**
     * Filter schedules by start hour.
     *
     * @param string $hour two-digit hour, as DateTime::format('H')
     * @return $this
     */
    public function setHourFilter($hour)
    {
        $this->addFieldToFilter('start_at', $hour);

        return $this;
    }

    /**
     * Filter schedules last processed before the given date.
     *
     * @param string $date
     * @return $this
     */
    public function setDateFilter($date)
    {
        $this->addFieldToFilter('processed_at', ['lt' => $date]);

        return $this;
    }
}
