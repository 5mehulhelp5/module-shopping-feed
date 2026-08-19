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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\Associated;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

/**
 * Creates Product url and appends suffix if its set
 *
 * Class Url
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\Associated;
 */
class Url extends MapperAbstract
{
    const XML_PATH_MICRODATA_ENABLED = 'mageos_shopping_feed/google/microdata_enabled';

    protected $scopeConfig;

    public function __construct(
        \MageOS\ShoppingFeed\Model\Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {

        $this->scopeConfig = $scopeConfig;
        parent::__construct($logger);
    }

    /**
     * @param  array $params
     * @return string
     */
    public function map(array $params = [])
    {
        $adapter = $this->getAdapter()->getParentAdapter();
        // @var $product \Magento\Catalog\Model\Product
        $product = $adapter->getProduct();

        $url = $product->getProductUrl();
        if (parse_url($url, PHP_URL_SCHEME) === null) {
            $baseUrl = $product->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
            $url = rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
        }
        $url = preg_replace('/#.*$/', '', $url);

        $query = trim((string) ($params['param'] ?? ''), '?&');

        $uniqueParams = $adapter->getUrlOptions($this->getAdapter()->getProduct());
        if (is_array($uniqueParams)) {
            $microdataEnabled = $this->scopeConfig->getValue(self::XML_PATH_MICRODATA_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $adapter->getFeed()->getStoreId());
            if ($microdataEnabled) {
                $query .= ($query === '' ? '' : '&') . 'aid=' . rawurlencode((string) $this->getAdapter()->getProduct()->getId());
            }
        }

        if ($query !== '') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . $query;
        }
        if (is_array($uniqueParams) && $uniqueParams !== []) {
            $url .= '#' . http_build_query($uniqueParams);
        }

        $adapter->getFilter()->findAndReplace($url, $params['column']);
        return $url;
    }
}
