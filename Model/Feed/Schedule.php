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

use Magento\Framework\Model\AbstractModel;

/**
 * Class Schedule
 *
 * @package MageOS\ShoppingFeed\Model\Feed
 *
 * @method $this   setFeedId(int $feedId)
 * @method int     getFeedId()
 * @method $this   setBatchLimit(int $limit)
 * @method int     getBatchLimit()
 * @method boolean getBatchMode()
 * @method $this   setBatchMode(boolean $mode)
 * @method $this   setProcessedAt(string $dateTime)
 */
class Schedule extends AbstractModel
{
    const BATCH_MODE_ENABLED = 1;
    const BATCH_MODE_DISABLE = 0;

    /**
     * Event prefix for observer
     *
     * @var string
     */
    protected $_eventPrefix = 'mageos_shopping_feed_feed_schedule';

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Framework\Locale\ResolverInterface             $localeResolver
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface    $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
     * @param array                                                   $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->localeResolver = $localeResolver;
        $this->localeDate = $localeDate;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageOS\ShoppingFeed\Model\ResourceModel\Feed\Schedule');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getFormattedSchedule()
    {
        $locale = $this->localeResolver->getLocale();

        $date = $this->localeDate->date()->setTime($this->getStartAt(), 0);
        $startAtFormatted = $this->localeDate->formatDateTime(
            $date,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $locale
        );

        if ($this->getBatchMode() != self::BATCH_MODE_ENABLED) {
            return __('Daily at %1', $startAtFormatted);
        }

        return __('Daily, starting at %1<br /> in batches of %2', $startAtFormatted, $this->getBatchLimit());
    }
}
