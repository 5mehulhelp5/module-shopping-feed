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

class IdentifierExists extends MapperAbstract
{
    const IDENTIFIER_FALSE = "FALSE";

    public function map(array $params = [])
    {
        $cacheMapValues = [];
        $identifiers = array_key_exists('param', $params) ? array_filter(explode(',', $params['param'])) : [];
        $identifiersToLoad = !empty($identifiers) ? $identifiers : ['brand', 'gtin', 'mpn'];

        foreach ($this->getAdapter()->getFeed()->getColumnsMap() as $map) {
            foreach ($identifiersToLoad as $column) {
                if ($map['column'] == $column) {
                    $cacheMapValues[$column] = $this->getAdapter()->getMapValue($map);
                }
            }
        }
        // Default params, or empty: special case for Google spec - gtin and mpn exclude each other
        if (empty($identifiers) || $identifiers == ['brand', 'gtin', 'mpn']) {
            $identifiers = ['brand'];
            // if gtin is missing, we'll check require mpn instead
            if (!array_key_exists('gtin', $cacheMapValues)
                || (array_key_exists('gtin', $cacheMapValues) && empty($cacheMapValues['gtin']))) {
                array_push($identifiers, 'mpn');
            }
        }

        $score = 0;
        foreach ($identifiers as $column) {
            if (array_key_exists($column, $cacheMapValues) && !empty($cacheMapValues[$column])) {
                $score++;
            }
        }

        $out = ($score == count($identifiers)) ? "" : self::IDENTIFIER_FALSE;
        $this->getAdapter()->getFilter()->findAndReplace($out, $params['column']);

        return $out;
    }
}
