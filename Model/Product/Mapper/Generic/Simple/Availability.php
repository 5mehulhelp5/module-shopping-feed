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

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperStock;

class Availability extends MapperStock
{
    const OUT_OF_STOCK = 'out_of_stock';
    const IN_STOCK     = 'in_stock';
    const BACKORDER    = 'backorder';
    const PREORDER     = 'preorder';

    public static $allowedStockStatuses = [self::OUT_OF_STOCK, self::IN_STOCK, self::BACKORDER, self::PREORDER];

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Status
     */
    protected $status;

    /**
     * @var MageOS\ShoppingFeed\Model\Inventory\Api
     */
    protected $sourceInventoryApi;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistryProvider
     */
    protected $stockRegistryProvider;


    public function __construct(
        \MageOS\ShoppingFeed\Model\Logger $logger,
        \Magento\CatalogInventory\Model\Stock\Status $status,
        \MageOS\ShoppingFeed\Model\Inventory\Api $sourceInventoryApi,
        \Magento\CatalogInventory\Model\StockRegistryProvider $stockRegistryProvider
    ) {
        $this->sourceInventoryApi = $sourceInventoryApi;
        $this->status = $status;
        $this->stockRegistryProvider = $stockRegistryProvider;
        parent::__construct($logger);
    }

    public function map(array $params = [])
    {
        $cell = $this->getStockStatus($this->getAdapter());
        return $this->getAdapter()->getFilter()->cleanField($cell, $params);
    }

    /**
     * @param  $adapter
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockStatus($adapter)
    {
        $cell = self::OUT_OF_STOCK;
        $product = $adapter->getProduct();

        if ($this->usesDefaultStock()) {
            $stockItem = $this->stockRegistryProvider->getStockItem($product->getId(), $product->getStoreId());
            $backOrdered = !is_null($stockItem) && (int)$stockItem->getData('backorders') > 0;

            if ($this->sourceInventoryApi->getAllItems($product)) {
                $storeCode = $this->getAdapter()->getFeed()->getStore()->getWebsite()->getCode();
                $sourceItems = $this->sourceInventoryApi->getItems($product, $storeCode);
                foreach ($sourceItems as $item) {
                    if ($item->getStatus() && $item->getQuantity() > 0) {
                        $cell = self::IN_STOCK;
                    } elseif ($item->getStatus() && $backOrdered) {
                        $cell = self::BACKORDER;
                    }
                }
            } else {
                $status = $this->status->load($product->getId());
                $isInStock = (bool)$status->getStockStatus();
                if ($isInStock && $status->getQty() > 0) {
                    $cell = self::IN_STOCK;
                } elseif ($isInStock && $backOrdered) {
                    $cell = self::BACKORDER;
                }
            }

            if ($cell == self::OUT_OF_STOCK && !$this->isStockManaged($stockItem)) {
                $cell = self::IN_STOCK;
            }

        } else {
            $stockAttribute = $adapter->getMapAttribute(
                ['attribute' => $this->getAdapter()->getFeed()->getConfig('general_stock_attribute_code')]
            );
            $stockStatus = trim(strtolower($adapter->getAttributeValue($product, $stockAttribute)));
            if (in_array($stockStatus, self::$allowedStockStatuses) === false) {
                $stockStatus = str_replace(' ', '_', $stockStatus);
                if (in_array($stockStatus, self::$allowedStockStatuses) === false) {
                    $stockStatus = self::OUT_OF_STOCK;
                }
            }

            $cell = $stockStatus;
        }
        return $cell;
    }

    public function isStockManaged($stockItem)
    {
        if ($stockItem && $stockItem->getItemId()) {
            return (bool)$stockItem->getManageStock();
        }
        return true;
    }

    public function filter($cell)
    {
        if (!$this->getAdapter()->getFeed()->getConfig('filters_add_out_of_stock')) {
            if ($cell == self::OUT_OF_STOCK) {
                return true;
            }
        }
        return false;
    }
}
