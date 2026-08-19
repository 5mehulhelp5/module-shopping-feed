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

/**
 * Class Price
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple
 */
class TierPrice extends MapperAbstract
{
    /**
     * @param  array $params
     * @return string
     */
    public function map(array $params = [])
    {
        $price = '';
        $product = $this->getAdapter()->getProduct();

        $customerGroup = array_key_exists('param', $params) ? $params['param'] : \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID;
        $groups = [\Magento\Customer\Model\Group::CUST_GROUP_ALL, $customerGroup];
        $rows = $product->setCustomerGroupId($customerGroup)->getTierPrice();

        $prices = [];
        foreach ($rows as $row) {
            if (in_array($row['cust_group'], $groups)) {
                $prices[] = $row['price'];
            }
        }
        if (!empty($prices)) {
            $price = sprintf("%.2F", min($prices));
        }

        return $price;
    }

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
