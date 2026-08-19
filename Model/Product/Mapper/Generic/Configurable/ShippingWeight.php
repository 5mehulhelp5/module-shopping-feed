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
use \MageOS\ShoppingFeed\Model\Exception as FeedException;

/**
 * Gets shipping weight and appends unit
 *
 * Class ShippingWeight
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable
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

        $associatedProductAdapters = $this->getAdapter()->getData('associated_product_adapters');

        $weights = [];
        $selected_weights = [];
        /**
 * @var \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $associatedProductAdapter
*/
        foreach ($associatedProductAdapters as $associatedProductAdapter) {
            $weightAttribute = $this->getAdapter()->getMapAttribute($params);
            if ($weightAttribute !== false) {
                $weights[] = number_format((float)$this->getAdapter()->getAttributeValue($associatedProductAdapter->getProduct(), $weightAttribute), 2);
            }

            // Use the weight of the product used when pulling the price.
            if (in_array($associatedProductAdapter->getProduct()->getId(), $this->getAdapter()->getData('min_price_product_ids'))) {
                array_push($selected_weights, end($weights));
            }
        }

        $weight = 0;
        if (count($selected_weights)) {
            $weight = min($selected_weights);
        } elseif (count($weights) > 0) {
            $weight = min($weights);
        }
        $weight = $weight ? sprintf('%s %s', $weight, $unit) : '';

        return $weight;
    }
}
