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
namespace MageOS\ShoppingFeed\Cron;

use MageOS\ShoppingFeed\Model\FeedFactory;
use MageOS\ShoppingFeed\Model\Generator\BatchFactory;
use MageOS\ShoppingFeed\Model\ResourceModel\Feed\Schedule\CollectionFactory as ScheduleCollectionFactory;
use MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue\CollectionFactory as QueueCollectionFactory;

class Schedule
{
    const XML_PATH_ENABLED = 'mageos_shopping_feed/general/cron_enabled';

    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\BatchFactory
     */
    protected $batchFactory;

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * @var ScheduleCollectionFactory
     */
    protected $scheduleCollectionFactory;

    /**
     * @var QueueCollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var bool.
     * Is set to you when process is initiated through console and not magento's cron
     */
    protected $detached = false;


    public function __construct(
        \MageOS\ShoppingFeed\Model\Generator\BatchFactory $batchFactory,
        \MageOS\ShoppingFeed\Model\FeedFactory $feedFactory,
        \MageOS\ShoppingFeed\Model\ResourceModel\Feed\Schedule\CollectionFactory $scheduleCollectionFactory,
        \MageOS\ShoppingFeed\Model\ResourceModel\Generator\Queue\CollectionFactory $queueCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {

        $this->batchFactory = $batchFactory;
        $this->feedFactory = $feedFactory;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->localeDate = $localeDate;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) ($this->scopeConfig->getValue(self::XML_PATH_ENABLED)) || $this->detached;
    }

    /**
     * Add feeds which should be generated to queue
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->isEnabled()) {
            return;
        }

        $queueCollection = $this->queueCollectionFactory->create();

        /**
 * @var \DateTime $dateObject
*/
        $dateObject = $this->localeDate->date();

        $yesterday = clone $dateObject;
        $yesterday->setTime(0, 0);
        $dateTimeFormat = \Magento\Framework\DB\Adapter\Pdo\Mysql::DATETIME_FORMAT;

        $scheduleCollection = $this->scheduleCollectionFactory->create();
        $scheduleCollection->setHourFilter($dateObject->format('H'))
            ->setDateFilter($yesterday->format($dateTimeFormat));

        /**
 * @var \MageOS\ShoppingFeed\Model\Feed\Schedule $schedule
*/
        foreach ($scheduleCollection as $schedule) {
            $queue = $queueCollection->getQueue($schedule->getFeedId());
            if (!$queue->getId()) {
                $feed = $this->feedFactory->create()
                    ->load($schedule->getFeedId());
                if ($schedule->getBatchMode()) {
                    $batch = $this->batchFactory->create();
                    $batch->setEnabled(true)
                        ->setLimit($schedule->getBatchLimit())
                        ->setOffset(0);
                    $queue->setBatch($batch);
                }
                // Add new queue for process
                $queue->add($feed, $schedule);
                $feed->saveStatus(\MageOS\ShoppingFeed\Model\Feed\Source\Status::STATUS_PENDING);
                $schedule->setProcessedAt($dateObject->format($dateTimeFormat));
                $schedule->save();
                $this->counter++;
            }
        }
    }

    public function getCounter()
    {
        return $this->counter;
    }

    public function setDetached()
    {
        $this->detached = true;
        return $this;
    }
}
