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

class Price extends MapperAbstract
{
    /**
     * Product price from the adapter, including tax when the column param is set.
     *
     * @param  array $params
     * @return float|int Price from the adapter's getPrices()
     */
    public function map(array $params = [])
    {
        $prices = $this->getAdapter()->getPrices();
        $key = $this->getKey(false, $params);
        $price = $prices[$key];

        return $price;
    }

    /**
     * Price array key for the regular or special price, including tax when the column param is set.
     *
     * @param  boolean $isSpecialPrice
     * @param  array   $params
     * @return string
     */
    protected function getKey($isSpecialPrice, $params)
    {
        $key = $isSpecialPrice ? 'sp_' : 'p_';
        if (isset($params['param']) && $params['param']) {
            // Add Tax
            $key .= 'incl_';
        } else {
            $key .= 'excl_';
        }
        $key .= 'tax';

        return $key;
    }

    /**
     * @inheritdoc
     */
    public function filter($cell)
    {
        $above = $this->getAdapter()->getFeed()->getConfig('filters_skip_price_above', false);
        $below = $this->getAdapter()->getFeed()->getConfig('filters_skip_price_below', false);

        if (($above !== false && $cell > $above) || ($below !== false && $cell < $below)) {
            return true;
        }

        return false;
    }
}
