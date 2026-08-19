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
use Magento\Catalog\Model\Product\Type as ProductType;

/**
 * Class Types
 */
class Types implements OptionSourceInterface
{
    /**
     * @var ProductType
     */
    protected $productType;

    /**
     * Types constructor.
     *
     * @param ProductType $productType
     */
    public function __construct(
        ProductType $productType
    ) {
        $this->productType = $productType;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getProductTypes() as $type) {
            $options[] = [
                'label' => $type['label'],
                'value' => $type['name'],
            ];
        }

        return $options;
    }

    /**
     * Retrieve product types
     *
     * @return array
     */
    public function getProductTypes()
    {
        return $this->productType->getTypes();
    }
}
