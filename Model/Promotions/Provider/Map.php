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

namespace MageOS\ShoppingFeed\Model\Promotions\Provider;

/**
 * Feed edit form Categories Map tab block
 */
class Map
{
    const APPLICABILITY_ALL         = 'all_products';
    const APPLICABILITY_SPECIFIC    = 'specific_products';
    const GENERIC_CODE              = 'generic_code';
    const NO_CODE                   = 'no_code';

    /**
     * @var  \MageOS\ShoppingFeed\Model\Promotions\Provider
     */
    protected $provider;

    /**
     * @var \MageOS\ShoppingFeed\Model\Serializer
     */
    protected $serializer;

    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @param \MageOS\ShoppingFeed\Model\Promotions\Provider $provider
     * @return $this
     */
    public function setProvider(\MageOS\ShoppingFeed\Model\Promotions\Provider  $provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * Creates promotion_id
     *
     * @param $counter
     * @param $rule
     * @return string
     */
    public function mapPromotionId($counter, $rule)
    {
        return sprintf('PROMO_%s_%s', $counter, $rule->getId());
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return string
     */
    public function mapProductApplicability(\Magento\SalesRule\Model\Rule $rule)
    {
        $resource = $rule->getResource();
        $actionsAttributes = $resource->getProductAttributes($rule->getActionsSerialized());
        $conditionsAttributes = $resource->getProductAttributes($rule->getConditionsSerialized());
        if (count($actionsAttributes) > 0 || count($conditionsAttributes) > 0) {
            return self::APPLICABILITY_SPECIFIC;
        }
        return self::APPLICABILITY_ALL;
    }

    /**
     * Prepare effective dates
     *
     * @param array $row
     * @return string
     */
    public function mapEffectiveDates($row = array())
    {
        return $this->mapDates($row['date']);
    }

    /**
     * Prepare display dates
     *
     * @param array $row
     * @return string
     */
    public function mapDisplayDates($row = array())
    {
        return $this->mapDates($row['display']);
    }

    /**
     * Helper method for dates
     *
     * @param array $row
     * @return string
     */
    public function mapDates($row = array())
    {
        $format = $this->provider->getPromotionDateFormat();
        $fromDate = $this->provider->prepareDate('', $row['from'], false, $format);
        $toDate = $this->provider->prepareDate('', $row['to'], false, $format);
        return $fromDate . "/" . $toDate;
    }

    /**
     * Set promotion type (coupon code / no code)
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return string
     */
    public function mapOfferType(\Magento\SalesRule\Model\Rule $rule)
    {
        return $rule->getCouponType() == 1 ? self::NO_CODE : self::GENERIC_CODE;
    }

    /**
     * Prepare if minimum purchase amount exists
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return string
     */
    public function mapMinimumPurchaseAmount(\Magento\SalesRule\Model\Rule $rule)
    {
        $conditions = $rule->getConditionsSerialized();
        $res = array('conditions' => array((array)json_decode($conditions)));
        if (json_last_error() != JSON_ERROR_NONE) {
            $res = $this->serializer->unserialize($conditions);
        }
        $conditions = count($res) > 0 ? $res : array();

        $minimumPurchaseAmount = 0;
        if (isset($conditions['conditions']) && is_array($conditions['conditions']) && count($conditions['conditions']) > 0) {
            foreach ($conditions['conditions'] as $condition) {
                if (
                    in_array($condition['attribute'], array('base_subtotal'))
                    && in_array($condition['operator'], array('>', '>=', '=='))
                    && is_numeric($condition['value'])
                    && $condition['value'] > 0
                    && $condition['value'] > $minimumPurchaseAmount
                ) {
                    $minimumPurchaseAmount = $condition['value'];
                }
            }
        }

        $currency = $this->provider->getFeed()->getStore()->getData('current_currency')->getCode();
        return $minimumPurchaseAmount > 0 ? sprintf("%.2F", $minimumPurchaseAmount) . ' ' . $currency : '';
    }

    /**
     * Returns coupon code if exists
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return string
     */
    public function mapGenericRedemptionCode(\Magento\SalesRule\Model\Rule $rule)
    {
        if ($rule->getCouponType() == 2) {
            return $rule->getCouponCode();
        }
        return '';
    }
}
