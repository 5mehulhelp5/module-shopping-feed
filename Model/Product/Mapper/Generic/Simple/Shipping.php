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

use MageOS\ShoppingFeed\Model\Generator\Cache;
use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

/**
 * Fetches shipping values from:
 * 1. Cache Class
 * 2. Cache/Shipping DB table
 * 3. Calculates the values and saves them in DB & Class cache
 *
 * Class Shipping
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple
 */
class Shipping extends MapperAbstract
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Cache\ShippingProvider
     */
    protected $shippingProvider;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        Cache $cache,
        \MageOS\ShoppingFeed\Model\Generator\Cache\ShippingProvider $shippingProvider,
        \MageOS\ShoppingFeed\Model\Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {

        $this->shippingProvider = $shippingProvider;
        $this->cache = $cache;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($logger);
    }

    /**
     * @param  array $params
     * @return string
     */
    public function map(array $params = [])
    {
        $allowedCountries = array_filter($this->getAdapter()->getFeed()->getConfig('shipping_country'));
        if (!is_array($allowedCountries) || count($allowedCountries) == 0) {
            return '';
        }

        // product cache
        $cacheKey = ['shipping', 'map', 'product', 'rate', $this->getAdapter()->getProduct()->getId()];
        $value = $this->cache->getCache($cacheKey, false);
        if ($value !== false && !is_null($value)) {
            return $value;
        }

        // product persistent cache
        $scheduledCurrencyUpdateEnabled = $this->scopeConfig->getValue(
            \Magento\Directory\Model\Observer::IMPORT_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $useShippingCache = $this->getAdapter()->getFeed()->getConfig('shipping_cache_enabled') && !$scheduledCurrencyUpdateEnabled;
        $cacheTtl = $useShippingCache ? $this->getAdapter()->getFeed()->getConfig('shipping_ttl') : 0;

        $this->shippingProvider->prepareCache($this->getAdapter(), $this->getAdapter()->getProduct(), $cacheTtl);
        if (($value = $this->shippingProvider->getCache()) !== false) {
            $this->shippingProvider->setCache($value);
            return $value;
        }

        try {
            $shipping = $this->shippingProvider->getShipping($this->getAdapter());

            // price_weight cache
            $weight = $shipping->getProductWeight($this->getAdapter()->getProduct());
            $price = $this->getAdapter()->getProduct()->getPrice();
            $secondCacheKey = ['shipping', 'map', 'price_weight', 'rate', $weight, $price];
            if (($value = $this->cache->getCache($secondCacheKey, false)) === false) {
                $request = $shipping->setRequest($this->getAdapter()->getProduct());
                $shipping->collectRates($request);
                $value = $shipping->getFormatedValue();
            }

            $this->cache->setCache($secondCacheKey, $value);
            $this->shippingProvider->setCache($value);
            $this->cache->setCache($cacheKey, $value);
            return $value;
        } catch (\Exception $e) {
            $this->getAdapter()->getLogger()->error(
                sprintf(
                    'ERROR when computing shipping value for product id %s: %s',
                    $this->getAdapter()->getProduct()->getId(),
                    $e->getMessage()
                )
            );
            return '';
        }
    }
}
