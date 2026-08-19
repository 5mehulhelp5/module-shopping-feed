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

namespace MageOS\ShoppingFeed\Model\Feed;

use MageOS\ShoppingFeed\Model\Feed\Source\Status;

class Copier
{
    /**
     * @var \MageOS\ShoppingFeed\Model\FeedFactory
     */
    protected $feedFactory;

    /**
     * @param \MageOS\ShoppingFeed\Model\FeedFactory $feedFactory
     */
    public function __construct(
        \MageOS\ShoppingFeed\Model\FeedFactory $feedFactory
    ) {
        $this->feedFactory = $feedFactory;
    }

    /**
     * Create feed duplicate
     *
     * @param  \MageOS\ShoppingFeed\Model\Feed $feed
     * @return \MageOS\ShoppingFeed\Model\Feed
     */
    public function copy(\MageOS\ShoppingFeed\Model\Feed $feed)
    {
        $duplicate = $this->feedFactory->create();
        $duplicate->setData($feed->getData());
        // clear messages for newly cloned feed
        $duplicate->setData('messages', '');
        $duplicate->setName($duplicate->getName() . '_clone');
        $duplicate->setId(null);
        $duplicate->setCreatedAt(null);
        $duplicate->setUpdatedAt(null);

        $schedules = $feed->getSchedules();
        if (!empty($schedules)) {
            foreach ($schedules as $index => &$schedule) {
                // remove 'id' key so that the schedule is recognized as a new entity in Feed::saveSchedules()
                unset($schedule['id']);
            }
            $duplicate->setData('schedules', $schedules);
        }

        $uploads = $feed->getUploads();
        if (!empty($uploads)) {
            foreach ($uploads as $index => &$upload) {
                unset($upload['id']);
            }
            $duplicate->setData('uploads', $uploads);
        }

        $duplicate->setStatus(Status::STATUS_DISABLED);
        if ($feed->getConfig()) {
            $duplicate->setConfig($feed->getConfig());
        }

        $duplicate->save();
        return $duplicate;
    }
}
