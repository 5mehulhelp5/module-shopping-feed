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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\Associated;

use \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\ProductTypeMagentoCategory as SimpleProductTypeMagentoCategory;

class ProductTypeMagentoCategory extends SimpleProductTypeMagentoCategory
{
    public function map(array $params = [])
    {
        // Return parent's value if exists.
        $value = $this->getAdapter()->getParentAdapter()->getMapValue($params);
        if (!empty($value)) {
            return $value;
        }
        return parent::map($params);
    }
}
