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

class ProductTypeByCategory extends MapperAbstract
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Category\CollectionProvider
     */
    protected $categoryProvider;

    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Cache
     */
    protected $cache;

    /**
     * ProductTypeByCategory constructor.
     *
     * @param \MageOS\ShoppingFeed\Model\Logger                              $logger
     * @param \MageOS\ShoppingFeed\Model\Product\Category\CollectionProvider $categoryProvider
     * @param \MageOS\ShoppingFeed\Model\Generator\Cache                     $cache
     */
    public function __construct(
        \MageOS\ShoppingFeed\Model\Logger $logger,
        \MageOS\ShoppingFeed\Model\Product\Category\CollectionProvider $categoryProvider,
        \MageOS\ShoppingFeed\Model\Generator\Cache $cache
    ) {
        $this->cache = $cache;
        $this->categoryProvider = $categoryProvider;
        parent::__construct($logger);
    }

    public function map(array $params = [])
    {

        $mapByCategory = $this->getSortedTaxonomyMap();
        $value = $this->matchByCategory($mapByCategory, $this->getAdapter()->getProduct()->getCategoryIds(), 'ty');

        $this->getAdapter()->getFilter()->findAndReplace($value, $params['column']);
        return html_entity_decode($value);
    }

    /**
     * Computes the taxonomy category array and sorted by priority and deepth according to the sorting mode
     *
     * @return mixed
     */
    public function getSortedTaxonomyMap()
    {
        $cacheKey = ['feed', $this->getAdapter()->getFeed()->getId(), 'taxonomy_map'];
        if (($map = $this->cache->getCache($cacheKey, false)) === false) {
            $map = $this->getAdapter()->getFeed()->getConfig('categories_provider_taxonomy_by_category', []);

            // Load categories to the the level path for each one
            $categories = $this->categoryProvider->getCategories($this->getAdapter()->getFeed());

            $sort = [
                'level' => [],
                'priority' => []
            ];

            foreach ($map as $categoryId => $data) {
                // Build a sort array by category level and priority
                $sort['level'][$categoryId] = isset($categories[$categoryId]) ? $categories[$categoryId]['level'] : 0;
                $sort['priority'][$categoryId] = $data['p'];
            }

            if ($this->getAdapter()->getFeed()->getConfig('categories_sort_mode') == \MageOS\ShoppingFeed\Model\Feed\Source\Category\PriorityMode::PRIORITY_AFTER_LEVEL) {
                array_multisort($sort['level'], SORT_DESC, $sort['priority'], SORT_ASC, $map);
            } else {
                array_multisort($sort['priority'], SORT_ASC, $map);
            }

            $this->cache->setCache($cacheKey, $map);
        }

        return $map;
    }
    /**
     * @param  $mapByCategory
     * @param  $categoryIds
     * @param  string $field
     * @return string
     */
    public function matchByCategory($mapByCategory, $categoryIds, $field = 'tx')
    {
        $value = '';
        if (!empty($categoryIds) && !empty($mapByCategory)) {
            foreach ($mapByCategory as $arr) {
                if (array_key_exists($field, $arr) && !empty($arr[$field])
                    && array_search($arr['id'], $categoryIds) !== false
                ) {
                    $value = $arr[$field];
                    break;
                }
            }
        }

        return $value;
    }
}
