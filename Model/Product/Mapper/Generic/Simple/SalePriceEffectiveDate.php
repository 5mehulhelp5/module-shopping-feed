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
 * Returns Sale price From => To dates in ISO 8601 format
 * Example: 2004-02-12T15:19:21+00:00/2005-02-12T15:19:21+00:00
 *
 * Class SalePriceEffectiveDate
 *
 */
class SalePriceEffectiveDate extends MapperAbstract
{
    /**
     * Sale price date range in ISO 8601, '' when there is none.
     *
     * @param  array $params
     * @return string
     */
    public function map(array $params = [])
    {
        $cell = '';
        $dates = $this->getAdapter()->getSalePriceEffectiveDates();
        if (is_array($dates)) {
            if (array_key_exists('start', $dates) && array_key_exists('end', $dates)) {
                /** @var \DateTime $start */
                $start = $dates['start'];
                /** @var \DateTime $end */
                $end = $dates['end'];
                $cell = $start->format('c') . "/" . $end->format('c');
            }
        }

        if (!empty($cell)) {
            $this->getAdapter()->getFilter()->findAndReplace($cell, $params['column']);
        }

        return $cell;
    }
}
