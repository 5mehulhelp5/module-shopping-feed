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

use \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\TierPrice as SimpleTierPrice;

/**
 * Class Price
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple
 */
class TierPrice extends SimpleTierPrice
{
    /**
     * @param  array $params
     * @return string
     */
    public function map(array $params = [])
    {
        $associatedProductAdapters = $this->getAdapter()->getData('associated_product_adapters');

        switch ($this->getAdapter()->getFeed()->getConfig('grouped_price_display_mode')) {
        case \MageOS\ShoppingFeed\Model\Feed\Source\Product\Grouped\PriceType::PRICE_SUM_DEFAULT_QTY:
            $totalPrice = 0;
            /**
 * @var \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $associatedProductAdapter
*/
            foreach ($associatedProductAdapters as $associatedProductAdapter) {
                $price = $associatedProductAdapter->getMapValue($params);
                $qty = $associatedProductAdapter->getProduct()->getQty();
                $totalPrice += $price * ($qty > 0 ? $qty : 1);
            }
            return $totalPrice;
        case \MageOS\ShoppingFeed\Model\Feed\Source\Product\Grouped\PriceType::PRICE_START_AT:
            $allPrices = [];
            /**
 * @var \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $associatedProductAdapter
*/
            foreach ($associatedProductAdapters as $associatedProductAdapter) {
                $price = $associatedProductAdapter->getMapValue($params);
                $allPrices[] = $price;
            }
            if (count($allPrices) == 0) {
                $this->getAdapter()->setSkipProduct(sprintf('Product skipped - no associated products found, price = 0. Product SKU #%s', $this->getAdapter()->getProduct()->getSku()));
                return '';
            }
            return min($allPrices);
        default:
            return '';
        }
    }
}
