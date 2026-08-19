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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

/**
 * Returns a CSV (or custom separator) list
 * of given attributes values
 *
 * Class VariantAttributes
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable
 */
class VariantAttributes extends MapperAbstract
{
    /**
     * @param  array $map
     * @return mixed|string
     * @throws \MageOS\ShoppingFeed\Model\Exception
     */
    public function map(array $map = [])
    {
        $attributesCodes = $map['param'];

        if (!is_array($attributesCodes) || count($attributesCodes) == 0) {
            return '';
        }

        $cell = $this->getAttributeValue($map, $this->getAdapter());
        $associatedProductAdapters = $this->getAdapter()->getData('associated_product_adapters');

        // Try get from associated products
        if ($cell == ""
            && is_array($associatedProductAdapters)
            && count($associatedProductAdapters) > 0
        ) {
            $values = [];
            $separator = $this->getAdapter()->getFeed()->getConfig('configurable_attribute_merge_value_separator');
            foreach ($associatedProductAdapters as $associatedProductAdapter) {
                $val = $this->getAttributeValue($map, $associatedProductAdapter);
                if (!in_array($val, $values)) { array_push($values, $val);
                }
            }
            $cell = implode($separator, $values);
        }

        return str_replace(",", " /", $cell);
    }

    /**
     * @param  array                                                          $map
     * @param  \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $adapter
     * @return string
     * @throws \MageOS\ShoppingFeed\Model\Exception
     */
    protected function getAttributeValue($map, $adapter)
    {
        $attributesCodes = $map['param'];
        $separator = $this->getAdapter()->getFeed()->getConfig('configurable_attribute_merge_value_separator');
        $product = $adapter->getProduct();

        $cell = '';
        // Try to match the proper attribute by looking at what product has loaded
        foreach ($attributesCodes as $attributeCode) {
            if (!empty($attributeCode) && $product->hasData($attributeCode)) {
                $attribute = $this->getAdapter()->getMapAttribute($attributeCode);
                $v = $this->getAdapter()->getFilter()->cleanField(
                    $adapter->getAttributeValue($product, $attribute),
                    $map
                );
                if ($v != "") {
                    $cell .= empty($cell) ? $v : $separator . $v;
                }
            }
        }

        return $cell;
    }
}
