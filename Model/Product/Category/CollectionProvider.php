<?php

namespace MageOS\ShoppingFeed\Model\Product\Category;

class CollectionProvider
{
    /**
     * Category collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {

        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param  \MageOS\ShoppingFeed\Model\Feed $feed
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategories(\MageOS\ShoppingFeed\Model\Feed $feed)
    {
        $parentId = $this->storeManager->getStore($feed->getStoreId())->getRootCategoryId();
        $level = $feed->getConfig('categories_category_depth', 8);

        $collection = $this->categoryCollectionFactory->create();

        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active')
            ->setStoreId($feed->getStoreId())
            ->addFieldToFilter(
                [
                ['attribute' => 'path', 'like' => "1/$parentId/%"],
                ['attribute' => 'path', 'eq' => "1/$parentId"]
                ]
            )->addLevelFilter($level)
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
            $categories, function ($a, $b) {
                return version_compare(
                    str_replace('/', '.', $a['path']),
                    str_replace('/', '.', $b['path'])
                );
            }
        );


        return $categories;
    }
}
