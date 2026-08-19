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

namespace MageOS\ShoppingFeed\Model\Feed\Source\Shipping;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Weight
 */
class Weight implements OptionSourceInterface
{
    const WEIGHT_UNIT_GRAM = 'g';
    const WEIGHT_UNIT_KILOGRAM = 'kg';
    const WEIGHT_UNIT_OUNCE = 'oz';
    const WEIGHT_UNIT_POUND = 'lb';

    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $options = [];
        foreach ($this->getOptionArray() as $value => $label) {
            $options[] = ['label' => $label, 'value' => $value];
        }
        $this->options = $options;

        return $this->options;
    }

    /**
     * Get options array
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::WEIGHT_UNIT_GRAM => __('gram'),
            self::WEIGHT_UNIT_KILOGRAM => __('kilogram'),
            self::WEIGHT_UNIT_OUNCE => __('ounce'),
            self::WEIGHT_UNIT_POUND => __('pound')
        ];
    }
}
