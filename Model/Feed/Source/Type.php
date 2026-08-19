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

/**
 * Class Type
 */
class Type implements OptionSourceInterface
{
    /**
     * @var \MageOS\ShoppingFeed\Model\FeedTypes\Config
     */
    protected $feedTypesConfig;

    /**
     * Type constructor.
     *
     * @param \MageOS\ShoppingFeed\Model\FeedTypes\Config $feedTypesConfig
     */
    public function __construct(
        \MageOS\ShoppingFeed\Model\FeedTypes\Config $feedTypesConfig
    ) {
        $this->feedTypesConfig = $feedTypesConfig;
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        $feedTypes = $this->feedTypesConfig->getAll();
        $feedTypeOptions = [];

        foreach ($feedTypes as $feedType) {
            $feedTypeOptions[$feedType['name']] = __($feedType['label']);
        }

        return $feedTypeOptions;
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
