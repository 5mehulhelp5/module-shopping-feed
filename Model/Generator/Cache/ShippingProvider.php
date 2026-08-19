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

namespace MageOS\ShoppingFeed\Model\Generator\Cache;

use Magento\Framework\Model\AbstractModel;

/**
 * Class ShippingProvider
 *
 * @package MageOS\ShoppingFeed\Model\Generator\Cache
 */
class ShippingProvider
{
    /**
     * @var \MageOS\ShoppingFeed\Model\ResourceModel\Shipping\CollectionFactory
     */
    protected $shippingCollectionFactory;

    /**
     * @var \MageOS\ShoppingFeed\Model\ResourceModel\Shipping\Collection
     */
    protected $shippingCollection;

    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Cache
     */
    protected $cache;

    /**
     * @var \MageOS\ShoppingFeed\Model\Product\ShippingFactory
     */
    protected $shippingFactory;

    /**
     * @var int
     */
    protected $cacheTtl = 0;

    /**
     * @var array
     */
    protected $cacheTemporaryData = [];

    public function __construct(
        \MageOS\ShoppingFeed\Model\Generator\Cache $cache,
        \MageOS\ShoppingFeed\Model\ResourceModel\Shipping\CollectionFactory $shippingCollectionFactory,
        \MageOS\ShoppingFeed\Model\Product\ShippingFactory $shippingFactory
    ) {

        $this->shippingCollectionFactory = $shippingCollectionFactory;
        $this->shippingFactory = $shippingFactory;
        $this->cache = $cache;
    }

    public function getShipping($adapter)
    {
        $adapterClassName = get_class($adapter);
        $adapterCacheKey = ['shipping', 'adapter', $adapterClassName];
        /**
 * @var \MageOS\ShoppingFeed\Model\Product\Shipping $shipping
*/
        if (($shipping = $this->cache->getCache($adapterCacheKey, false)) === false) {
            $shipping = $this->shippingFactory->create(['adapter' => $adapter]);
            $this->cache->setCache($adapterCacheKey, $shipping);
        }
        return $shipping;
    }

    public function prepareCache(
        \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterInterface $adapter,
        \Magento\Catalog\Model\Product $product,
        $cacheTtl = 0
    ) {

        $this->cacheTtl = $cacheTtl;
        $this->shippingCollection = $this->shippingCollectionFactory->create();
        $this->shippingCollection->filterByFeed($adapter->getFeed())
            ->filterByProduct($product)
            ->filterByStore($adapter->getStore())
            ->filterByCurrencyCode($adapter->getStore()->getCurrentCurrency()->getCode());

        $this->setTemporaryCacheData($adapter, $product);
        return $this;
    }

    public function setTemporaryCacheData(
        \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterInterface $adapter,
        \Magento\Catalog\Model\Product $product
    ) {

        $this->cacheTemporaryData = [
            'adapter' => $adapter,
            'product' => $product
        ];
        return $this;
    }

    public function getCache()
    {
        /**
 * @var \DateTime $date
*/
        $date = $this->cacheTemporaryData['adapter']->getTimezone()->date();
        $date->sub(new \DateInterval(sprintf('P%sD', $this->cacheTtl)));
        $date = $date->format('Y-m-d H:i:s');
        $collection = clone $this->shippingCollection;
        $collection->filterByDate($date)
            ->setPageSize(1);

        if ($collection->getSize() > 0) {
            $cacheItem = $collection->getFirstItem();
            return $cacheItem->getValue();
        }
        return false;
    }

    public function setCache($value)
    {
        // We only save cache if its enabled & longer then 0
        if ($this->cacheTtl > 0) {
            /**
 * @var \MageOS\ShoppingFeed\Model\Shipping $cacheItem
*/
            $cacheItem = $this->shippingCollection->setPageSize(1)
                ->getFirstItem();
            if (!$cacheItem->getId()) {
                /**
                 * @var \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterInterface $adapter
                 * @var \Magento\Catalog\Model\Product $product
                 */
                extract($this->cacheTemporaryData);
                $cacheItem->setProductId($product->getId())
                    ->setStoreId($adapter->getStore()->getStoreId())
                    ->setFeedId($adapter->getFeed()->getId())
                    ->setCurrencyCode($adapter->getStore()->getCurrentCurrency()->getCode());
            }
            // The value might be the same but cache could expire, so we need to set the new date
            // Which doesn't happen if tha origData == data
            $date = $this->cacheTemporaryData['adapter']->getTimezone()->date();
            $date = $date->format('Y-m-d H:i:s');

            $cacheItem->setUpdatedAt($date)
                ->setValue($value)
                ->save();
        }

        return $this;
    }

    /**
     * Clear cache per feed
     *
     * @param  \MageOS\ShoppingFeed\Model\Feed $feed
     * @return \MageOS\ShoppingFeed\Model\Generator\Cache\ShippingProvider
     */
    public function clearCache(\MageOS\ShoppingFeed\Model\Feed $feed)
    {
        $shippingCollection = $this->shippingCollectionFactory->create();
        $shippingCollection->filterByFeed($feed);
        foreach ($shippingCollection as $item) {
            $item->delete();
        }
        return $this;
    }
}
