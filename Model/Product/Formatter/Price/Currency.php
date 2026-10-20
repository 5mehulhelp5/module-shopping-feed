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

namespace MageOS\ShoppingFeed\Model\Product\Formatter\Price;

use MageOS\ShoppingFeed\Model\Product\Formatter\FormatterAbstract;

class Currency extends FormatterAbstract
{
    /**
     * Format a positive numeric price with two decimals and the store currency; anything else becomes empty.
     *
     * @param mixed $var Cell value
     * @return mixed
     */
    public function run($var)
    {
        $val = is_numeric($var) && (float)$var > 0
            ? sprintf("%.2F %s", $var, $this->getAdapter()->getData('store_currency_code'))
            : '';
        if (!empty($val) && $this->getColumn() !== null) {
            $this->getAdapter()->getFilter()->findAndReplace($val, $this->getColumn());
        }
        return $val;
    }
}
