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
class AssociatedMode implements OptionSourceInterface
{
    const ONLY_PARENT = 0;
    const ONLY_ASSOCIATED = 1;
    const BOTH_PARENT_ASSOCIATED = 2;

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            self::ONLY_ASSOCIATED        => __('No parent product / Only associated products'),
            self::ONLY_PARENT            => __('Only parent / No associated products'),
            self::BOTH_PARENT_ASSOCIATED => __('Both types - parent product and associated products'),
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
