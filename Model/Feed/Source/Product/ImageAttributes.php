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
 * Class ImageAttributes
 */
class ImageAttributes implements OptionSourceInterface
{
    /**
     * Image attributes cache
     *
     * @var array
     */
    protected $imageAttributes;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $productAttributeCollectionFactory;

    /**
     * Types constructor.
     *
     * @param ProductType $productType
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttributeCollectionFactory
    ) {
        $this->productFactory = $productFactory;
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getImageAttributes() as $column) {
            $options[] = [
                'label' => $column['label'],
                'value' => $column['code'],
            ];
        }

        return $options;
    }

    /**
     * Retrieve image attributes
     *
     * @return array
     */
    public function getImageAttributes()
    {
        if ($this->imageAttributes !== null) {
            return $this->imageAttributes;
        }

        $attributes = [];

        $attributeCollection = $this->productAttributeCollectionFactory->create();
        $attributeCollection->setFrontendInputTypeFilter('media_image');
        $attributeCollection->setEntityTypeFilter($this->productFactory->create()->getTypeId());

        foreach ($attributeCollection as $attribute) {
            $attributes[] = [
                'code' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontend()->getLabel()
            ];
        }

        $this->imageAttributes = $attributes;

        return $this->imageAttributes;
    }
}
