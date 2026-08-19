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
 * @copyright Copyright (c) 2023 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */

namespace MageOS\ShoppingFeed\Model\Product\Mapper\LocalInventory\Configurable;

use MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\Availability as ConfigurableAvailability;


class Availability extends ConfigurableAvailability
{
    public function map(array $params = [])
    {
        $count = [0];
        $cell = self::OUT_OF_STOCK;
        $associatedProductAdapters = $this->getAdapter()->getData('associated_product_adapters');
        $sourceItem = $this->getAdapter()->getData('inventory_source_item');

        foreach ($associatedProductAdapters as $associatedProductAdapter) {
            $count[] = $associatedProductAdapter->getInventoryCount($sourceItem ? $sourceItem->getSourceCode(): null);
        }
        $qty = max($count);

        if ($qty > 0) {
            $cell = self::IN_STOCK;
        }

        $product = $this->getAdapter()->getProduct();
        $stockItem = $this->stockRegistryProvider->getStockItem($product->getId(), $product->getStoreId());
        if (!is_null($stockItem)
            && (int)$stockItem->getData('is_in_stock') > 0
            && (int)$stockItem->getData('backorders') > 0
            && (int)$stockItem->getData('qty') <= 0) {
            $cell = self::BACKORDER;
        }

        if ($cell == self::OUT_OF_STOCK && !$this->isStockManaged($stockItem)) {
            $cell = self::IN_STOCK;
        }

        return $this->getAdapter()->getFilter()->cleanField($cell, $params);
    }
}
