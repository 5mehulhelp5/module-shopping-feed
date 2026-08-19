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

use \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Price as SimplePrice;

/**
 * Class Price
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple
 */
class Price extends SimplePrice
{
    /**
     * @param  array $params
     * @return string
     */
    public function map(array $params = [])
    {
        $key = $this->getKey(false, $params);

        return $this->getPrice($key);
    }

    protected function getPrice($key)
    {
        $minPrice = PHP_FLOAT_MAX;
        $associatedProductAdapters = $this->getAdapter()->getData('associated_product_adapters');
        $thePrices = [];

        /**
 * @var \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $associatedProductAdapter
*/
        foreach ($associatedProductAdapters as $associatedProductAdapter) {
            $prices = $associatedProductAdapter->getPrices();
            $thePrices[$associatedProductAdapter->getProduct()->getId()] = $prices[$key];
            if ($prices[$key] < $minPrice) {
                $minPrice = $prices[$key];
            }
        }

        $theProductIds = [];
        foreach ($thePrices as $id => $price) {
            if ($price == $minPrice) {
                array_push($theProductIds, $id);
            }
        }
        $this->getAdapter()->setData('min_price_product_ids', $theProductIds);

        if ($minPrice == PHP_FLOAT_MAX) {
            $this->getAdapter()->setSkipProduct(sprintf('Product skipped - no associated products found, price = 0. Product SKU #%s', $this->getAdapter()->getProduct()->getSku()));
            return 0;
        }
        return $minPrice;
    }
}
