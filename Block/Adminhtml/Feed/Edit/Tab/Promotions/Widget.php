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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Promotions;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Element\AbstractArrayElement;

/**
 * Adminhtml Manage Uploads renderer
 */
class Widget extends AbstractArrayElement implements RendererInterface
{
    /**
     * @var string
     */
    protected $controlName = 'promotionsControl';

    /**
     * @var string
     */
    protected $_template = 'feed/edit/tab/promotions/widget.phtml';

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $sourceYesno;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \MageOS\ShoppingFeed\Model\Promotions\Provider
     */
    protected $provider;

    /**
     * @var \MageOS\ShoppingFeed\Model\Promotions\Provider\Collection
     */
    protected $promotionsCollection;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\Config\Source\Yesno $sourceYesno
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        \Magento\Framework\Registry $coreRegistry,
        \MageOS\ShoppingFeed\Model\Promotions\Provider $provider,
        \MageOS\ShoppingFeed\Model\Promotions\Provider\Collection $promotionsCollection,
        array $data = []
    ) {
        $this->sourceYesno = $sourceYesno;
        $this->coreRegistry = $coreRegistry;
        $this->provider = $provider;
        $this->promotionsCollection = $promotionsCollection;

        // Set the current feed in provider
        $feed = $this->coreRegistry->registry('feed');
        $this->provider->setFeed($feed);
        //$this->promotionsCollection->setFeed($feed);

        parent::__construct($context, $data);
    }

    /**
     * Sort uploads values callback method
     *
     * @param array $a
     * @param array $b
     * @return int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function sortValuesCallback($a, $b)
    {
        if ($a['id'] != $b['id']) {
            return $a['id'] < $b['id'] ? -1 : 1;
        }
        return 0;
    }

    /**
     * Retrieve Yes/No options
     * @return array
     */
    public function getYesnoOptions()
    {
        return $this->sourceYesno->toOptionArray();
    }

    /**
     * Retrieve form element instance
     * @return bool
     */
    public function getControlName()
    {
        return $this->controlName;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->provider->getDateFormat();
    }

    /**
     * @return \Magento\SalesRule\Model\ResourceModel\Rule\Quote\Collection
     */
    public function getMagentoCartRules()
    {
        return $this->promotionsCollection->getPromotionRules($this->provider->getFeed());
    }

    /**
     * @param $key
     * @param string $default
     * @return string
     */
    public function getPromotionData($key, $default = '')
    {
        $this->provider->validateGooglePromotions();
        $data = $this->provider->getFeed()->getConfig('promotions_provider_widget');

        return !empty($data[$key]) ? $data[$key] : $default;
    }

    /**
     * Merges the saved data with default data
     * Handles dates from timestamp/date to LOCALE date format
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param array $row
     * @return array
     */
    public function getPromotionRow(\Magento\SalesRule\Model\Rule $rule, $row = [])
    {
        $fromDate = isset($row['date']) && isset($row['date']['from']) ? $row['date']['from'] : '';
        $fromDate = $this->provider->prepareDate($rule->getFromDate(), $fromDate);
        $toDate = isset($row['date']) && isset($row['date']['to']) ? $row['date']['to'] : '';
        $toDate = $this->provider->prepareDate($rule->getToDate(), $toDate, $fromDate);

        if (!isset($row['date'])) {
            $row['date'] = [];
        }
        $row['date']['from'] = $fromDate;
        $row['date']['to'] = $toDate;

        if (!isset($row['display'])) {
            $row['display'] = [];
        }
        if (isset($row['display']['from'])) {
            $row['display']['from'] = $this->provider->prepareDate('', $row['display']['from']);
        } else {
            $row['display']['from'] = $fromDate;
        }
        if (isset($row['display']['to'])) {
            $row['display']['to'] = $this->provider->prepareDate('', $row['display']['to']);
        } else {
            $row['display']['to'] = $toDate;
        }

        $default = [
            'include' => 0,
            'title' => $rule->getName(),
            'date' => [
                'from' => $fromDate,
                'to' => $toDate
            ],
            'display' => [
                'from' => $fromDate,
                'to' => $toDate
            ]
        ];

        return array_replace_recursive($default, $row);
    }

    /**
     * Checks if cart rule could be related to shipping and
     * coupon code is not set (which is not allowed by
     * Google Promotions Program Policies)
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return bool
     */
    public function isShippingWithoutCoupon(\Magento\SalesRule\Model\Rule $rule)
    {
        if ($rule->getApplyToShipping() && $rule->getCouponType() == 1) {
            return true;
        }
        return false;
    }
}
