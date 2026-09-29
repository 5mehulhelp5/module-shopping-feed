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
        // Missing catalog data is not evidence that the manufacturer assigned no identifiers.
        $out = ($params['param'] ?? '') === 'no_identifiers' ? self::IDENTIFIER_FALSE : '';
        foreach ($this->getAdapter()->getFeed()->getColumnsMap() as $map) {
            if (in_array($map['column'], ['brand', 'gtin', 'mpn'], true)
                && trim((string)$this->getAdapter()->getMapValue($map)) !== ''
            ) {
                $out = '';
                break;
            }
        }
        $this->getAdapter()->getFilter()->findAndReplace($out, $params['column']);

        return $out;
    }
}
