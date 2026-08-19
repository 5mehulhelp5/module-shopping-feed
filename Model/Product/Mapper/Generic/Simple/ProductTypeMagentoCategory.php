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

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

class ProductTypeMagentoCategory extends MapperAbstract
{
    /**
     * Don't include those categories in the path building.
     */
    const SKIP_CATEG_NAMES = ['default', 'default category', 'root', 'root catalog'];

    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Cache
     */
    protected $cache;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * ProductTypeMagentoCategory constructor.
     *
     * @param \MageOS\ShoppingFeed\Model\Logger          $logger
     * @param \MageOS\ShoppingFeed\Model\Generator\Cache $cache
     * @param \Magento\Catalog\Model\CategoryFactory         $categoryFactory
     */
    public function __construct(
        \MageOS\ShoppingFeed\Model\Logger $logger,
        \MageOS\ShoppingFeed\Model\Generator\Cache $cache,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->cache = $cache;
        $this->categoryFactory = $categoryFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($logger);
    }

    public function map(array $params = [])
    {
        $return = [];
        $maxValues = !empty($params['param']) ? intval($params['param']) : 3;

        $categoryIds = $this->getAdapter()->getProduct()->getCategoryIds();
        if (is_null($categoryIds)) {
            $categoryIds = [];
        }

        // Exclude disabled categories from the Categories Map tab
        $map = $this->getAdapter()->getFeed()->getConfig('categories_provider_taxonomy_by_category', []);
        $disabledCategories = array_filter($map, [$this, 'filterDisabled']);
        $categoryIds = array_diff($categoryIds, array_keys($disabledCategories));

        // Exclude categories not in store
        $rootCategoryId = $this->getAdapter()->getStore()->getRootCategoryId();
        $storeCategories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('id')
            ->addIsActiveFilter()
            ->addFieldToFilter('path', ['like' => "1/{$rootCategoryId}/%"]);
        $storeCategoryIds = array_keys($storeCategories->exportToArray());
        $categoryIds = array_intersect($categoryIds, $storeCategoryIds);

        // Compose the category paths
        foreach ($categoryIds as $categoryId) {
            $cacheKey = ['row', 'map', 'category', $categoryId, 'path'];
            $names = $this->cache->getCache($cacheKey, false);
            if (false === $names) {
                $names = $this->getCategoryNames($categoryId);
                $this->cache->setCache($cacheKey, $names);
            }
            if (count($names)) {
                $return[implode(' > ', $names)] = count($names);
            }
        }

        // Limit the output
        arsort($return);
        $value = array_slice(array_keys($return), 0, $maxValues);
        $value = implode(', ', $value);

        return $this->getAdapter()->getFilter()->cleanField($value, $params);
    }

    /**
     * Get array of category names path of the current categoryId
     *
     * @param  $categoryId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \MageOS\ShoppingFeed\Model\Exception
     */
    protected function getCategoryNames($categoryId)
    {
        $category = $this->categoryFactory->create()
            ->setStore($this->getAdapter()->getStore())
            ->load($categoryId);

        $names = [];
        if (!$category->getIsActive()) {
            return $names;
        }

        // Loop through each category of the path
        $pathList = explode('/', $category->getPath());

        $categories = $this->categoryCollectionFactory->create()->addAttributeToSelect('name')
            ->setStore($this->getAdapter()->getStore())
            ->addIsActiveFilter()
            ->addAttributeToFilter('entity_id', ['in' => $pathList])
            ->setOrder('path', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        foreach ($categories as $cat) {
            $categoryName = $cat->getName();
            if (empty($categoryName)) {
                continue;
            }
            if (!in_array(strtolower($categoryName), self::SKIP_CATEG_NAMES)) {
                array_push($names, $categoryName);
            }
        }

        return $names;
    }

    /**
     * Callback for array_filter, removes all elements but the one disabled.
     *
     * @param  $row
     * @return bool
     */
    public function filterDisabled($row)
    {
        return !isset($row['d']) || empty($row['d']);
    }
}
