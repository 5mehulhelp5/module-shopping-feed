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
 * @copyright Copyright (c) 2021 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */

namespace MageOS\ShoppingFeed\Model\Inventory;

class Api
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Creates MSI services only when MSI is enabled
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Whether Magento MSI (Magento_InventoryApi) is enabled.
     *
     * @return bool
     */
    public function isMsiEnabled()
    {
        return $this->moduleManager->isEnabled('Magento_InventoryApi');
    }

    /**
     * Source items of the product whose enabled sources are linked to the website's stock.
     *
     * @param mixed $product catalog product
     * @param string $websiteCode
     * @return array
     */
    public function getItems($product, $websiteCode)
    {

        if (!$this->isMsiEnabled()) {
            return [];
        }

        $stockResolver = $this->objectManager->create(\Magento\InventorySalesApi\Api\StockResolverInterface::class);
        $stock = $stockResolver->execute("website", $websiteCode);
        $stockId = (int)$stock->getStockId();

        $sourceCodes = [];
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('stock_id', $stockId)->create();
        $stockLinks = $this->objectManager->create(\Magento\InventoryApi\Api\GetStockSourceLinksInterface::class);
        $sourceRepository = $this->objectManager->create(\Magento\InventoryApi\Api\SourceRepositoryInterface::class);
        foreach ($stockLinks->execute($searchCriteria)->getItems() as $link) {
            if ($sourceRepository->get($link->getSourceCode())->isEnabled()) {
                $sourceCodes[] = $link->getSourceCode();
            }
        }

        $items = [];
        $sourceItems = $this->getAllItems($product);
        foreach ($sourceItems as $item) {
            if (in_array($item['source_code'], $sourceCodes)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * All MSI source items of the product, or an empty array without MSI.
     *
     * @param mixed $product catalog product
     * @return array
     */
    public function getAllItems($product)
    {

        if (!$this->isMsiEnabled()) {
            return [];
        }

        $sourceItems = $this->objectManager->create(\Magento\InventoryApi\Api\GetSourceItemsBySkuInterface::class);
        return $sourceItems->execute($product->getSku());
    }

    /**
     * Sum of reservation quantities for the SKU in the website's stock or the source's stock.
     *
     * @param string $sku
     * @param string|null $sourceCode
     * @param string|null $websiteCode Resolve reservations for the feed's website stock.
     * @return float|int
     */
    public function getReservations(string $sku, ?string $sourceCode = null, ?string $websiteCode = null)
    {
        if (!$this->isMsiEnabled()) {
            return 0;
        }
        $connection = $this->resourceConnection->getConnection();
        $reservationTable = $this->resourceConnection->getTableName('inventory_reservation');
        $select = $connection->select()
            ->from($reservationTable, ['quantity' => 'SUM(quantity)'])
            ->where('sku = ?', $sku);

        if ($websiteCode !== null) {
            $resolver = $this->objectManager->create(\Magento\InventorySalesApi\Api\StockResolverInterface::class);
            $stockId = (int)$resolver->execute('website', $websiteCode)->getStockId();
            $select->where('stock_id = ?', $stockId);
        } elseif ($sourceCode !== null) {
            $inventoryTableName = $this->resourceConnection->getTableName('inventory_source_stock_link');
            $sourceSelect = $connection->select()->from($inventoryTableName, ['stock_id'])
                ->where('source_code = ?', $sourceCode);
            $stockId = $connection->fetchOne($sourceSelect);
            if ($stockId) {
                $select->where("stock_id = ?", $stockId);
            } else {
                return 0;
            }
        }

        $reservationQty = $connection->fetchOne($select);
        return $reservationQty ? (float)$reservationQty : 0;
    }
}
