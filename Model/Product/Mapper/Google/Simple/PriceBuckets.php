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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Google\Simple;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

class PriceBuckets extends MapperAbstract
{
    public function map(array $params = [])
    {
        $values = [];
        $buckets = $this->getAdapter()->getFeed()->getConfig('filters_adwords_price_buckets', false);

        if ($buckets) {
            $prices = $this->getAdapter()->getPrices();
            $price = $this->getAdapter()->hasSpecialPrice() ? $prices['sp_excl_tax'] : $prices['p_excl_tax'];
            foreach ($buckets as $bucket) {
                if (floatval($bucket['pricefrom']) <= floatval($price) && floatval($price) < floatval($bucket['priceto'])) {
                    array_push($values, $bucket['label']);
                }
            }
        }

        $cell = implode(',', $values);
        $this->getAdapter()->getFilter()->findAndReplace($cell, $params['column']);
        return $cell;
    }
}
