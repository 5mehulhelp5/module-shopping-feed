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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Bundle;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;
use \MageOS\ShoppingFeed\Model\Exception as FeedException;

/**
 * Gets shipping weight and appends unit
 *
 * Class ShippingWeight
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Bundle
 */
class ShippingWeight extends MapperAbstract
{
    /**
     * @param array $params
     */
    public function map(array $params = [])
    {
        $params['attribute'] = 'weight';
        $unit = $params['param'];

        // @var $product Mage_Catalog_Model_Product
        $product = $this->getAdapter()->getProduct();

        // Get weight attribute
        $weightAttribute = $this->getAdapter()->getMapAttribute($params);
        if ($weightAttribute === false) {
            throw new FeedException(
                new \Magento\Framework\Phrase(sprintf('Couldn\'t find attribute \'%s\'.', $params['attribute']))
            );
        }

        if ($this->getAdapter()->getFeed()->getConfig('bundle_combined_weight') || !$product->getData('weight_type')) {
            $associatedProductAdapters = $this->getAdapter()->getData('associated_product_adapters');
            $weight = 0;
            foreach ($associatedProductAdapters as $associatedProductAdapter) {
                /**
 * @var $associatedProductAdapter \MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple
*/
                $val = $associatedProductAdapter->getAttributeValue($associatedProductAdapter->getProduct(), $weightAttribute);
                $weight += floatval($val);
            }
        } else {
            $weight = $this->getAdapter()->getAttributeValue($product, $weightAttribute);
        }

        $weight = number_format((float)$weight, 2);
        $weight = $weight ? sprintf('%s %s', $weight, $unit) : '';


        return $weight;
    }
}
