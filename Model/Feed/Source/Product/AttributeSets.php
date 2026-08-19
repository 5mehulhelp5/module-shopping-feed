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
 * Class AttributeSet
 */
class AttributeSets implements OptionSourceInterface
{
    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\Options
     */
    protected $attributeSetOptions;

    public function __construct(
        \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSetOptions
    ) {
        $this->attributeSetOptions = $attributeSetOptions;
    }

    /**
     * Add default option
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->attributeSetOptions->toOptionArray();
        $defaultOption = [
            'label' => __('All attribute sets'),
            'value' => null
        ];
        array_unshift($options, $defaultOption);

        return $options;
    }
}
