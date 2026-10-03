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
        return $this->mapDates($row['date'] ?? []);
    }

    /**
     * Prepare display dates
     *
     * @param array $row
     * @return string
     */
    public function mapDisplayDates($row = array())
    {
        return $this->mapDates($row['display'] ?? []);
    }

    /**
     * Helper method for dates
     *
     * @param array $row
     * @return string
     */
    public function mapDates($row = array())
    {
        if (!is_array($row) || !is_string($row['from'] ?? null) || !is_string($row['to'] ?? null)
            || trim($row['from']) === '' || trim($row['to']) === '') {
            return '';
        }
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
        $serialized = $rule->getConditionsSerialized();
        if ($serialized === null || $serialized === '') {
            return '';
        }
        try {
            $conditions = $this->serializer->unserialize($serialized);
        } catch (\InvalidArgumentException $exception) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Promotion rule conditions must contain valid JSON. Resave the cart price rule.'), $exception
            );
        }
        if (!is_array($conditions)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid promotion rule conditions.'));
        }

        $minimumPurchaseAmount = $this->minimumSubtotal($conditions);
        if ($minimumPurchaseAmount <= 0) {
            return '';
        }
        $currency = $this->provider->getFeed()->getStore()->getData('current_currency')->getCode();
        return sprintf("%.2F", $minimumPurchaseAmount) . ' ' . $currency;
    }

    /** Compute a lower bound without treating OR or negated conditions as AND. */
    private function minimumSubtotal(array $condition): float
    {
        if (isset($condition['conditions']) && is_array($condition['conditions'])) {
            if (!in_array($condition['value'] ?? null, [1, '1', true], true)
                || !in_array($condition['aggregator'] ?? null, ['all', 'any'], true)
                || !$condition['conditions']) {
                return 0;
            }
            $amounts = array_map(fn($child) => is_array($child) ? $this->minimumSubtotal($child) : 0,
                $condition['conditions']);
            return $condition['aggregator'] === 'all' ? max($amounts) : min($amounts);
        }
        if (($condition['attribute'] ?? null) !== 'base_subtotal'
            || !in_array($condition['operator'] ?? null, ['>', '>=', '=='], true)
            || !is_numeric($condition['value'] ?? null) || (float)$condition['value'] <= 0) {
            return 0;
        }
        $amount = (float)$condition['value'];
        return $condition['operator'] === '>' ? (floor($amount * 100) + 1) / 100 : $amount;
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
