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

namespace MageOS\ShoppingFeed\Model\Product\Adapter\Type;

use \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract;
use \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterInterface;

/**
 * Simple Adapter, holds business logic between Product,Config and Mapper
 *
 * Class Simple
 *
 * @package MageOS\ShoppingFeed\Model\Product\Adapter
 */
class Simple extends AdapterAbstract implements AdapterInterface
{
    /**
     * Get the price array (regular/special + incuding/excluding tax)
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getProductPrices(\Magento\Catalog\Model\Product $product)
    {
        if ($this->getParentAdapter()) {
            $parentProduct = $this->getParentAdapter()->getProduct();

            // Get configurable option prices (Mageworks_Advanced_Product_Options)
            if ($parentProduct->getOptions()) {

                $price = $product->getPrice();
                foreach ($parentProduct->getOptions() as $option) {
                    if ($option->getValues() && $option->getIsRequire()) {
                        $val_price = 0;
                        foreach ($option->getValues() as $value) {
                            if ($value->getIsDefault()) {
                                $val_price = $value->getPrice();
                            } else {
                                $val_price = $val_price == 0 ? $value->getPrice() : min($val_price, $value->getPrice());
                            }
                        }
                        $price += $val_price;
                    }
                }
                $product->setPrice($price);
            }
        }

        return parent::getProductPrices($product);
    }
}
