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

use Magento\Catalog\Model\CategoryFactory;
use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

class CategoryImageLink extends MapperAbstract
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Cache
     */
    protected $cache;

    protected $categoryFactory;

    /**
     * CategoryImageLink constructor.
     *
     * @param \MageOS\ShoppingFeed\Model\Logger          $logger
     * @param \MageOS\ShoppingFeed\Model\Generator\Cache $cache
     * @param CategoryFactory                                $categoryFactory
     */
    public function __construct(
        \MageOS\ShoppingFeed\Model\Logger $logger,
        \MageOS\ShoppingFeed\Model\Generator\Cache $cache,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->cache = $cache;
        parent::__construct($logger);
    }

    public function map(array $params = [])
    {
        $image = '';
        /**
 * @var \Magento\Catalog\Model\Product $product
*/
        $product = $this->getAdapter()->getProduct();
        $categoryIds = $product->getCategoryIds();
        if (count($categoryIds) > 0) {
            $cacheKey = implode('-', $categoryIds);
            if ($this->cache->getCache($cacheKey, true)) {
                /**
 * @var \Magento\Catalog\Model\Category $category
*/
                foreach ($categoryIds as $categoryId) {
                    $category = $this->categoryFactory->create()->load($categoryId);
                    if (!$category->hasChildren() && ($imageUrl = $category->getImageUrl())) {
                        $this->cache->setCache($cacheKey, $imageUrl);
                        break;
                    }
                }
            }
            $image = $this->cache->getCache($cacheKey, '');
        }

        $this->getAdapter()->getFilter()->findAndReplace($image, $params['column']);
        return $image;
    }
}
