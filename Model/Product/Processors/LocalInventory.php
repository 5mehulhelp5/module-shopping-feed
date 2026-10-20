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
namespace MageOS\ShoppingFeed\Model\Product\Processors;

class LocalInventory extends \MageOS\ShoppingFeed\Model\Product\Processors\ProcessorAbstract
{
    /**
     * Dynamic directives which should be cached using inventorySourceId in the cacheKey
     *
     * @var string[]
     */
    public static $directives = ['directive_availability', 'directive_inventory_source', 'directive_quantity'];

    /**
     * Associated-products modes that output the parent product rows
     *
     * @var int[]
     */
    protected $allowed_parent = [
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode::ONLY_PARENT,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode::BOTH_PARENT_ASSOCIATED
    ];

    /**
     * Associated-products modes that output the associated product rows
     *
     * @var int[]
     */
    protected $allowed_assoc = [
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode::ONLY_ASSOCIATED,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode::BOTH_PARENT_ASSOCIATED
    ];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Local inventory processor constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->objectManager = $objectManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Replace the rows with one row per enabled inventory source of the feed's website stock.
     *
     * @param array $rows
     * @return array
     */
    // phpcs:ignore Generic.Metrics.NestingLevel.TooHigh -- legacy feed logic; refactor deferred to keep output identical
    public function execute(array $rows): array
    {
        $items = [];

        if ($this->usesDefaultStock() && $this->isMsiEnabled()) {

            // Get a list of source codes to restrict search of source items by product.
            $websiteCode = $this->getAdapter()->getFeed()->getStore()->getWebsite()->getCode();
            $stockResolver = $this->objectManager->create(
                \Magento\InventorySalesApi\Api\StockResolverInterface::class
            );
            $stock = $stockResolver->execute("website", $websiteCode);
            $stockId = (int)$stock->getStockId();

            $sourceCodes = [];
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('stock_id', $stockId)->create();
            $stockLinks = $this->objectManager->create(\Magento\InventoryApi\Api\GetStockSourceLinksInterface::class);
            $sourceRepository = $this->objectManager->create(
                \Magento\InventoryApi\Api\SourceRepositoryInterface::class
            );
            foreach ($stockLinks->execute($searchCriteria)->getItems() as $link) {
                if ($sourceRepository->get($link->getSourceCode())->isEnabled()) {
                    $sourceCodes[] = $link->getSourceCode();
                }
            }
            if (!$sourceCodes) {
                return [];
            }

            $associatedProductAdapters = $this->getAdapter()->getData('associated_product_adapters');

            $complexMode = \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode::ONLY_PARENT;
            if (method_exists($this->getAdapter(), 'getAssociatedProductsMode')) {
                $complexMode = $this->getAdapter()->getAssociatedProductsMode();
            }

            $itemParts = [];
            try {
                // Iterate inventory sources for current product
                if (in_array($complexMode, $this->allowed_parent)) {
                    $product = $this->getAdapter()->getProduct();
                    if ($product->getTypeId() === 'configurable') {
                        // Configurable inventory belongs to its children, not the parent SKU.
                        $sourceItems = [];
                        foreach ($associatedProductAdapters ?: [] as $associatedAdapter) {
                            foreach ($this->getSourceItems($associatedAdapter->getProduct(), $sourceCodes) as $item) {
                                $sourceItems[$item->getSourceCode()] = $item;
                            }
                        }
                    } else {
                        $sourceItems = $this->getSourceItems($product, $sourceCodes);
                    }
                    foreach ($sourceItems as $sourceItem) {
                        $adapter = $this->getAdapter();
                        $this->prepareAdapter($adapter, $sourceItem);
                        $itemParts[] = $this->getAdapter()->internalMap(false, false);
                    }
                }

                // Iterate inventory sources for associated products
                if ($associatedProductAdapters && in_array($complexMode, $this->allowed_assoc)) {
                    foreach ($associatedProductAdapters as $associatedProductAdapter) {
                        $sourceItems = $this->getSourceItems($associatedProductAdapter->getProduct(), $sourceCodes);
                        foreach ($sourceItems as $sourceItem) {
                            $this->prepareAdapter($associatedProductAdapter, $sourceItem);
                            $this->getAdapter()->setData(
                                'associated_product_adapters',
                                [$associatedProductAdapter]
                            );
                            /**
                             * Only composite adapters define getAssociatedProductsMode() and hold associated adapters.
                             *
                             * @var \MageOS\ShoppingFeed\Model\Product\Adapter\Type\Composite $compositeAdapter
                             */
                            $compositeAdapter = $this->getAdapter();
                            $itemParts[] = $compositeAdapter->mapAssociatedProducts(false);
                        }
                    }
                }
            } finally {
                $this->getAdapter()->setData('associated_product_adapters', $associatedProductAdapters);
            }
            return array_merge($items, ...$itemParts);
        }

        return $rows;
    }

    /**
     * Uses MSI to get source items for a product
     *
     * @param mixed $product Catalog product whose SKU is looked up
     * @param string[] $sourceCodes
     * @return array
     */
    protected function getSourceItems($product, $sourceCodes): array
    {
        if (!$this->isMsiEnabled()) {
            return [];
        }

        $searchCriteriaSourceItems = $this->searchCriteriaBuilder
            ->addFilter('source_code', $sourceCodes, 'in')
            ->addFilter('sku', $product->getSku(), 'eq')
            ->create();

        $sourceItemsRepository = $this->objectManager->create(
            \Magento\InventoryApi\Api\SourceItemRepositoryInterface::class
        );
        return $sourceItemsRepository->getList($searchCriteriaSourceItems)
            ->getItems();
    }

    /**
     * Extends local inventory specific directives cache key to include sourceId.
     *
     * Also sets the current sourceItem used by Mapper classes to generate output.
     *
     * @param \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $adapter
     * @param \Magento\InventoryApi\Api\Data\SourceItemInterface&\Magento\Framework\DataObject $sourceItem
     * @return void
     */
    protected function prepareAdapter($adapter, $sourceItem): void
    {

        // Add cache key to column in the map for self::$directives
        $map = $adapter->getFeed()->getColumnsMap();
        foreach ($map as $k => $column) {
            if (in_array($column['attribute'], self::$directives)) {
                $map[$k]['cache_key'] = $sourceItem->getSourceItemId();
            }
        }

        // Update the map and set sourceItem context for adapter(s)
        $adapter->getFeed()->setColumnsMap($map);
        $adapter->setData('inventory_source_item', $sourceItem);
    }

    /**
     * Whether the feed reads stock from the default stock rather than a custom stock attribute.
     *
     * @return bool
     */
    protected function usesDefaultStock(): bool
    {
        $useDefaultStock = $this->getAdapter()->getFeed()->getConfig('general_use_default_stock');
        $customStockAttribute = $this->getAdapter()->getFeed()->getConfig('general_stock_attribute_code');
        return $useDefaultStock || empty($customStockAttribute);
    }

    /**
     * Whether the MSI inventory API module is enabled.
     *
     * @return bool
     */
    protected function isMsiEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Magento_InventoryApi');
    }
}
