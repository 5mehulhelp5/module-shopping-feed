<?php

namespace MageOS\ShoppingFeed\Model\Product;

class CollectionProvider
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stock;

    /**
     * @var null|int
     */
    protected $testSku = null;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\CatalogInventory\Helper\Stock $stock
    ) {

        $this->collectionFactory = $collectionFactory;
        $this->stock = $stock;
    }

    /**
     * @param \MageOS\ShoppingFeed\Model\Feed $feed
     */
    public function getCollection($feed)
    {
        $collection = $this->collectionFactory->create();
        $collection->setStoreId($feed->getStoreId())
            ->addStoreFilter($feed->getStoreId());

        $collection = $this->addProductTypeToFilter($collection, $feed);

        // Filter visible / enabled products
        $collection->addAttributeToFilter(
            'status', [
            'neq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
            ]
        );
        $collection->addAttributeToFilter(
            'visibility', [
            'neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
            ]
        );

        $includeAllProducts = $feed->getConfig('categories_include_all_products');
        $categoryMap = $feed->getConfig('categories_provider_taxonomy_by_category', [], '');

        // include the root categoryId (a.k.a. default category) by default so that products assigned to it exclusively aren't excluded
        $excludeCategories = [];

        foreach ($categoryMap as $category) {
            if (isset($category['id']) && isset($category['d']) && (bool)$category['d'] === false) {
                $excludeCategories[] = (int)$category['id'];
            }
        }

        $collection = $this->addCategoriesToFilter($collection, $excludeCategories, (bool)$includeAllProducts);

        $filterAttributeSets = $feed->getConfig('filters_attribute_sets', []);
        if (count($filterAttributeSets) && empty($filterAttributeSets[0])) {
            array_shift($filterAttributeSets);
        }
        $attributeSets = !empty($filterAttributeSets) ? $filterAttributeSets : false;
        if ($attributeSets) {
            $collection->addAttributeToFilter('attribute_set_id', $attributeSets);
        }

        if (!$feed->getConfig('filters_add_out_of_stock')) {
            $collection->addPriceData(null);
            $this->stock->addInStockFilterToCollection($collection);
        }

        if (!is_null($this->testSku)) {
            $collection->addAttributeToFilter('sku', $this->testSku);
        }

        return $collection;
    }

    public function setTestSku($testSku)
    {
        $this->testSku = $testSku;
        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \MageOS\ShoppingFeed\Model\Feed                     $feed
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function addProductTypeToFilter($collection, $feed)
    {
        /**
 * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
*/
        $defaultProductTypes = [
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
            \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
        ];

        $productTypes = (array) $feed->getConfig('filters_product_types', []);
        $notInProductTypes = array_diff($defaultProductTypes, $productTypes);
        $inProductTypes = array_diff($productTypes, $defaultProductTypes);

        if (count($inProductTypes)) {
            $collection->addAttributeToFilter('type_id', ['in' => $productTypes]);
        }

        if (count($notInProductTypes) > 0) {
            $collection->addAttributeToFilter('type_id', ['nin' => $notInProductTypes]);
        }

        return $collection;
    }

    /**
     * Adds category ids to collection filter, adding join to category-product table if needed
     *
     * @param $collection         \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @param $categoryIds        int[]
     * @param $includeAllProducts boolean
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function addCategoriesToFilter($collection, $categoryIds, $includeAllProducts)
    {
        $where = [];
        if (count($categoryIds) > 0 || !$includeAllProducts) {
            $joinCond = 'cat_product.product_id=e.entity_id';
            $fromPart = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);

            if (isset($fromPart['cat_product'])) {
                $fromPart['cat_product']['joinCondition'] = $joinCond;
                $collection->getSelect()->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
            } else {
                $collection->getSelect()->joinInner(
                    ['cat_product' => $collection->getTable('catalog_category_product')],
                    $joinCond,
                    ['category_id' => 'cat_product.category_id',
                        'product_id' => 'cat_product.product_id',
                        'position' => 'cat_product.position']
                );
            }
        }

        if (!$includeAllProducts) {
            $where[] = 'cat_product.category_id IS NOT NULL';
        }

        if (count($categoryIds) > 0) {
            $cond = $collection->getConnection()->quoteInto('cat_product.category_id NOT IN (' . implode(',', $categoryIds) . ')', "");
            if ($includeAllProducts) {
                $cond .= ' OR cat_product.category_id IS NULL';
            }
            $where[] = $cond;
        }

        if (count($where) > 0) {
            $where = '(' . implode(' AND ', $where) . ')';
            $collection->getSelect()->where($where);
        }

        $collection->getSelect()->group('e.entity_id');
        return $collection;
    }
}
