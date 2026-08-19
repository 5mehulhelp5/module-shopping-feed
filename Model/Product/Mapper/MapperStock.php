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

namespace MageOS\ShoppingFeed\Model\Product\Mapper;

/**
 * Abstract class, defining main final methods
 *
 * Class MapperAbstract
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper
 */
abstract class MapperStock extends MapperAbstract
{
    /**
     * @return bool
     */
    public function usesDefaultStock()
    {
        $useDefaultStock = $this->getAdapter()->getFeed()->getConfig('general_use_default_stock');
        $customStockAttribute = $this->getCustomStockAttributeCode();
        return $useDefaultStock || (!$useDefaultStock && empty($customStockAttribute));
    }

    /**
     * @return string/null
     */
    public function getCustomStockAttributeCode()
    {
        return $this->getAdapter()->getFeed()->getConfig('general_stock_attribute_code');
    }
}
