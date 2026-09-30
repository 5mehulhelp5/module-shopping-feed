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

namespace MageOS\ShoppingFeed\Controller\Adminhtml\Feed;

use Magento\Framework\App\RequestInterface;

class Builder
{
    /**
     * @var \MageOS\ShoppingFeed\Model\FeedFactory
     */
    protected $feedFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Model\Session $session
     */
    protected $session;

    /**
     * @param \MageOS\ShoppingFeed\Model\FeedFactory $feedFactory
     * @param \Psr\Log\LoggerInterface                   $logger
     * @param \Magento\Framework\Registry                $registry
     * @param \Magento\Backend\Model\Session             $session
     */
    public function __construct(
        \MageOS\ShoppingFeed\Model\FeedFactory $feedFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Session $session
    ) {
        $this->feedFactory = $feedFactory;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->session = $session;
    }

    /**
     * Build feed based on user request
     *
     * @param  array
     * @return \MageOS\ShoppingFeed\Model\Feed
     */
    public function build($formData)
    {
        $feedId = $this->nonNegativeInteger($formData['id'] ?? 0, 'id');
        $storeId = $this->nonNegativeInteger($formData['store_id'] ?? 0, 'store_id');
        $typeId = $formData['type'] ?? null;
        if ($typeId !== null && !is_string($typeId)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid feed type.'));
        }
        $useMicrodata = $this->nonNegativeInteger(
            $formData['use_microdata'] ?? ($typeId !== 'generic' ? 1 : 0), 'use_microdata'
        );
        if ($useMicrodata > 1) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid use_microdata value.'));
        }
        /**
 * @var $feed \MageOS\ShoppingFeed\Model\Feed
*/
        $feed = $this->feedFactory->create();
        $feed->setStoreId($storeId);

        if (!$feedId && $typeId) {
            $feed->setType($typeId);
        }

        $feed->setUseMicrodata($useMicrodata);

        if ($feedId) {
            try {
                $feed->load($feedId);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }

        if (isset($formData['schedules'])) {
            $feed->setSchedules($formData['schedules']);
        } elseif ($feed->isObjectNew() && empty($feed->getSchedules())) {
            $feed->setSchedules([['id' => null, 'feed_id' => null, 'start_at' => 1, 'batch_mode' => 0, 'batch_limit' => '']]);
        }

        $this->registry->register('feed', $feed);

        $currentFeed = null;
        $sessionData = $this->session->getMageosShoppingFeedData(true);
        if (!empty($sessionData)) {
            $currentFeed = $sessionData;
        }
        $this->registry->register('current_feed', $currentFeed);

        return $feed;
    }

    private function nonNegativeInteger($value, string $field): int
    {
        if ((is_int($value) || is_string($value)) && ctype_digit((string)$value)) {
            $integer = filter_var(ltrim((string)$value, '0') ?: '0', FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 0]]);
            if ($integer !== false) {
                return $integer;
            }
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('Invalid %1 value.', $field));
    }
}
