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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Google\Simple;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

class IdentifierAttribute extends MapperAbstract
{
    public function map(array $params = [])
    {
        $cell = '';
        $product = $this->getAdapter()->getProduct();
        $attributeCode = $params['param'];

        if (!empty($attributeCode) && $product->hasData($attributeCode)) {
            $attribute = $this->getAdapter()->getMapAttribute($attributeCode);
            $cell = $this->getAdapter()->getAttributeValue($product, $attribute);
        }

        return $this->getAdapter()->getFilter()->cleanField($cell, $params);
    }
}
