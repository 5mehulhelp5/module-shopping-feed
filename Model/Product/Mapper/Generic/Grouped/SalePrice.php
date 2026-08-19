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

/**
 * Returns sale price if it exists (hasSpecialPrice())
 *
 * Class SalePrice
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Grouped
 */
class SalePrice extends Price
{
    /**
     * @param  array $params
     * @return string
     */
    public function map(array $params = [])
    {
        if (!$this->getAdapter()->hasSpecialPrice()) {
            return '';
        }

        $specialPriceKey = $this->getKey(true, $params);
        $priceKey = $this->getKey(false, $params);

        $specialPrice = $this->getPrice($specialPriceKey);
        $price = $this->getPrice($priceKey);

        if ($specialPrice > 0) {
            $specialPrice = $price <= $specialPrice ? 0 : $specialPrice;
        }

        return $specialPrice;
    }

    public function filter($cell)
    {
        return intval($cell) > 0 ? parent::filter($cell) : false;
    }
}
