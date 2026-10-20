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

namespace MageOS\ShoppingFeed\Model\Feed\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    public const STATUS_DISABLED       = 0;
    public const STATUS_SCHEDULED      = 1;
    public const STATUS_PENDING        = 2;
    public const STATUS_PROCESSING     = 3;
    public const STATUS_COMPLETED      = 4;
    public const STATUS_ERROR          = 5;

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            self::STATUS_DISABLED            => __('Disabled'),
            self::STATUS_SCHEDULED           => __('Scheduled'),
            self::STATUS_PENDING             => __('Pending'),
            self::STATUS_PROCESSING          => __('Processing'),
            self::STATUS_COMPLETED           => __('Completed'),
            self::STATUS_ERROR               => __('Error'),
        ];
    }

    /**
     * Get options as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
