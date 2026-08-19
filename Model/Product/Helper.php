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

namespace MageOS\ShoppingFeed\Model\Product;

class Helper
{
    /**
     * @var \Magento\Msrp\Model\Config
     */
    protected $msrp;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    protected $stockRegistry;

    public function __construct(
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\Msrp\Model\Config $msrp
    ) {

        $this->msrp = $msrp;
        $this->stockRegistry = $stockRegistry;
    }


    /**
     * Calculate the quantity increments including minimal sale quantity
     *
     * @param  \Magento\Catalog\Model\Product      $product
     * @param  \MageOS\ShoppingFeed\Model\Feed $feed
     * @return float
     */
    public function getQuantityIcrements(
        \Magento\Catalog\Model\Product $product,
        \MageOS\ShoppingFeed\Model\Feed $feed
    ) {

        $qtyIncrements = 1.0;
        if ($feed->getConfig('general_use_qty_increments')) {
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
            if ($stockItem) {
                if (!is_null($stockItem->getMinSaleQty())) {
                    $qtyIncrements = $stockItem->getMinSaleQty();
                }

                if ($stockItem->getQtyIncrements()) {
                    if ($qtyIncrements > 1.0) {
                        $qtyIncrementsTmp = $stockItem->getQtyIncrements();
                        if ($qtyIncrements % $qtyIncrementsTmp != 0) {
                            $nextIncrement = ceil($qtyIncrements / $qtyIncrementsTmp);
                            $qtyIncrements = $nextIncrement * $qtyIncrementsTmp;
                        }
                    } else {
                        $qtyIncrements = $stockItem->getQtyIncrements();
                    }
                }
            }
        }
        return $qtyIncrements;
    }

    /**
     * @param  \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasMsrp(\Magento\Catalog\Model\Product $product)
    {
        return $this->msrp->isEnabled()
        && $product->hasMsrp()
        && ($product->getPrice() < $product->getMsrp());
    }
}
