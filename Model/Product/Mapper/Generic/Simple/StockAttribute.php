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
 * @copyright Copyright (c) 2025 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;


class StockAttribute extends MapperAbstract
{
    /**
     * @var \Magento\CatalogInventory\Model\StockRegistryProvider
     */
    protected $stockRegistryProvider;

    public function __construct(
        \MageOS\ShoppingFeed\Model\Logger $logger,
        \Magento\CatalogInventory\Model\StockRegistryProvider $stockRegistryProvider
    ) {
        $this->stockRegistryProvider = $stockRegistryProvider;
        parent::__construct($logger);
    }

    /**
     * @param  array $params
     * @return string
     */
    public function map(array $params = [])
    {
        $attr_code = isset($params['param']) ? $params['param'] : 'backorders';
        $product = $this->getAdapter()->getProduct();

        $stockItem = $this->stockRegistryProvider->getStockItem($product->getId(), $product->getStoreId());
        $value = !is_null($stockItem) ? $stockItem->getData($attr_code) : '';
        if (is_null($value)) {
            $value = '';
        }

        return $this->getAdapter()->getFilter()->cleanField($value, $params);
    }
}
