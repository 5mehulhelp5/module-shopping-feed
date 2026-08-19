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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Grouped;

use \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Price as SimplePrice;

/**
 * Class Price
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Grouped
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
        $price = $this->getPrice($key);

        return $price;
    }

    protected function getPrice($key)
    {
        $associatedProductAdapters = $this->getAdapter()->getData('associated_product_adapters');
        $allPrices = [];
        $minPrice = 0;

        /**
 * @var \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $associatedProductAdapter
*/
        foreach ($associatedProductAdapters as $associatedProductAdapter) {
            $prices = $associatedProductAdapter->getPrices();
            $prices['qty'] = intval($associatedProductAdapter->getProduct()->getQty());
            $allPrices[] = $prices;
            $minPrice =  $minPrice == 0 ? $prices[$key] : min($prices[$key], $minPrice);
        }

        if (count($allPrices) == 0) {
            $this->getAdapter()->setSkipProduct(sprintf('Product skipped - no associated products found, price = 0. Product SKU #%s', $this->getAdapter()->getProduct()->getSku()));
            return 0;
        }

        switch ($this->getAdapter()->getFeed()->getConfig('grouped_price_display_mode')) {
        case \MageOS\ShoppingFeed\Model\Feed\Source\Product\Grouped\PriceType::PRICE_SUM_DEFAULT_QTY:
            $totalPrice = 0;
            foreach ($allPrices as $price) {
                $totalPrice += $price['qty'] > 0 ? ($price[$key] * $price['qty']) : 0;
            }
            return $totalPrice > 0 ? $totalPrice : $minPrice;
        case \MageOS\ShoppingFeed\Model\Feed\Source\Product\Grouped\PriceType::PRICE_START_AT:
        default:
            return $minPrice;
        }
    }
}
