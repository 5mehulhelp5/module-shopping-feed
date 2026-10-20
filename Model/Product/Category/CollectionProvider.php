<?php

namespace MageOS\ShoppingFeed\Model\Product\Category;

class CollectionProvider
{
    /**
     * Creates the category collection read for the feed's store
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Resolves the feed's store and its root category
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Category collection provider constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {

        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Categories under the store's root category up to the feed's depth, sorted by path, with child counts.
     *
     * @param  \MageOS\ShoppingFeed\Model\Feed $feed
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategories(\MageOS\ShoppingFeed\Model\Feed $feed)
    {
        /** @var \Magento\Store\Model\Store $store StoreManager loads stores through StoreRepository */
        $store = $this->storeManager->getStore($feed->getStoreId());
        $parentId = $store->getRootCategoryId();
        $level = $feed->getConfig('categories_category_depth', 8);

        $collection = $this->categoryCollectionFactory->create();

        // EAV addFieldToFilter() returns the collection itself; continue on the typed variable.
        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active')
            ->setStoreId($feed->getStoreId())
            ->addFieldToFilter(
                [
                ['attribute' => 'path', 'like' => "1/$parentId/%"],
                ['attribute' => 'path', 'eq' => "1/$parentId"]
                ]
            );
        $collection->addLevelFilter($level)
            ->addAttributeToSort('path', \Magento\Catalog\Model\ResourceModel\Category\Collection::SORT_ORDER_ASC);

        $categories = [];

        /**
 * @var \Magento\Catalog\Model\Category $category
*/
        foreach ($collection as $category) {
            $categories[$category->getId()] = [
                'name'          => $category->getName(),
                'id'            => $category->getId(),
                'path'          => $category->getPath(),
                'parent_id'     => $category->getParentId(),
                'level'         => $category->getLevel(),
                'store_active'  => $category->getIsActive(),
                'children'      => 0
            ];

            if (isset($categories[$category->getParentId()])) {
                $categories[$category->getParentId()]['children']++;
            }
        }

        usort(
            $categories,
            function ($a, $b) {
                return version_compare(
                    str_replace('/', '.', $a['path']),
                    str_replace('/', '.', $b['path'])
                );
            }
        );

        return $categories;
    }
}
