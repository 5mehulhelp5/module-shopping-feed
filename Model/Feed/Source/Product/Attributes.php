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
 * Class Attributes
 */
class Attributes implements OptionSourceInterface
{
    /**
     * Attributes cache
     *
     * @var array
     */
    protected $attributes;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * Types constructor.
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Eav\Model\Config   $eavConfig
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Get options
     *
     * @param  bool $withEmpty
     * @return array
     */
    public function toOptionArray($withEmpty = false)
    {
        if ($this->attributes !== null) {
            return $this->attributes;
        }

        $attributeOptions = [];
        foreach ($this->getAttributes() as $attribute) {
            $attributeOptions[] = [
                'label' => sprintf('%s (%s)', $attribute['label'], $attribute['code']),
                'value' => $attribute['code'],
            ];
        }

        if ($withEmpty) {
            array_unshift($attributeOptions, ['value' => '', 'label' => __('-- Please Select --')]);
        }

        return $this->attributes = $attributeOptions;
    }

    /**
     * Conveniance method for Store -> Configiration purpose.
     *
     * @return array
     */
    public function toOptionArrayWithEmpty()
    {
        return $this->toOptionArray(true);
    }

    /**
     * Retrieve attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = [];

        $attributeCodes = $this->eavConfig->getEntityAttributeCodes(\Magento\Catalog\Model\Product::ENTITY);

        foreach ($attributeCodes as $code) {
            $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $code);

            if ($attribute->getId()) {
                $attributes[] = [
                    'code' => $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontend()->getLabel()
                ];
            }
        }

        usort(
            $attributes, function ($a, $b) {
                if ($a['label'] != $b['label']) {
                    return $a['label'] < $b['label'] ? -1 : 1;
                }

                return 0;
            }
        );

        return $attributes;
    }
}
