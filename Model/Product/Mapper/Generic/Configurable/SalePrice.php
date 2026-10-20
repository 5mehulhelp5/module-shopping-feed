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

/**
 * Returns sale price if it exists (hasSpecialPrice())
 *
 * Class SalePrice
 *
 */
class SalePrice extends Price
{
    /**
     * Lowest sale price among the associated products, 0 without a special price.
     *
     * @param  array $params
     * @return float|int 0 when the product has no sale price
     */
    public function map(array $params = [])
    {
        if (!$this->getAdapter()->hasSpecialPrice()) {
            return 0;
        }

        $key = $this->getKey(true, $params);

        return $this->getPrice($key);
    }

    /**
     * @inheritdoc
     */
    public function filter($cell)
    {
        return (int) $cell > 0 ? parent::filter($cell) : false;
    }
}
