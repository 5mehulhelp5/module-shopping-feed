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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

/**
 * Returns a CSV (or custom separator) list
 * of given attributes values
 *
 * Class VariantAttributes
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple
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

        $cell = '';
        $product = $this->getAdapter()->getProduct();

        // Try to match the proper attribute by looking at what product has loaded
        foreach ($attributesCodes as $attributeCode) {
            if (!empty($attributeCode) && $product->hasData($attributeCode)) {
                $attribute = $this->getAdapter()->getMapAttribute(['attribute' => $attributeCode]);
                $v = $this->getAdapter()->getFilter()->cleanField(
                    $this->getAdapter()->getAttributeValue($product, $attribute),
                    $map
                );
                if ($v != "") {
                    $cell .= empty($cell) ? $v : $this->getAdapter()->getFeed()->getConfig('configurable_attribute_merge_value_separator') . $v;
                }
            }
        }

        // Try get from parent as it may be a non super-attribute value.
        if ($cell == "" && $this->getAdapter()->hasParentAdapter()) {
            $cell = $this->getAdapter()->getParentAdapter()->getMapValue($map);
        }
        return str_replace(",", " /", $cell);
    }
}
