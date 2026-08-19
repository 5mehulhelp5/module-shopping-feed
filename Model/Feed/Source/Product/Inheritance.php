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

namespace MageOS\ShoppingFeed\Model\Feed\Source\Product;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Mode
 */
class Inheritance implements OptionSourceInterface
{
    const ASSOCIATED_ONLY = 0;
    const ASSOCIATED_FIRST = 1;
    const PARENT_ONLY = 2;
    const PARENT_FIRST = 3;

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            self::ASSOCIATED_FIRST  => __('associated if exists, otherwise from parent'),
            self::ASSOCIATED_ONLY   => __('associated only'),
            self::PARENT_FIRST      => __('parent if exists, otherwise from associated'),
            self::PARENT_ONLY       => __('parent only')
        ];
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getOptionArray() as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }
}
