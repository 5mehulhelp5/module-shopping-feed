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

namespace MageOS\ShoppingFeed\Model\Taxonomy\Type;

use \MageOS\ShoppingFeed\Model\Taxonomy\ProviderInterface;
use \MageOS\ShoppingFeed\Model\Taxonomy\ProviderAbstract;

/**
 * Google Shopping Taxonomy Provider
 */
class GoogleShopping extends ProviderAbstract implements ProviderInterface
{
    const URL_FORMAT = 'https://www.google.com/basepages/producttype/taxonomy.%s.txt';

    const DEFAULT_LOCALE = 'en-US';

    protected $taxonomy;

    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     */
    protected $curlFactory;

    /**
     * @var \MageOS\ShoppingFeed\Model\Serializer
     */
    protected $serializer;

    /**
     * GoogleShopping constructor.
     *
     * @param \MageOS\ShoppingFeed\Model\Feed $feed
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     */
    public function __construct(
        \MageOS\ShoppingFeed\Model\Feed $feed,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->curlFactory = $curlFactory;
        $this->serializer = $serializer;
        parent::__construct($feed, $cache);
    }

    /**
     * Generates taxonomy list
     *
     * @return array
     */
    public function getTaxonomyList()
    {
        if (!is_null($this->taxonomy)) {
            return $this->taxonomy;
        }

        $cache = false;
        if ($this->getCacheKey() && $this->getCacheLifetime()) {
            $cache = $this->cache->load($this->getCacheKey());
        }

        if ($cache) {
            $this->taxonomy = $this->serializer->unserialize($cache);
            return $this->taxonomy;
        }

        $taxonomyData = $this->getTaxonomyData();

        if (false === $taxonomyData) {
            return [];
        }

        array_walk($taxonomyData, function (&$taxonomy, $key) {
            $taxonomy = [
                'id' => $key,
                'label' => $taxonomy
            ];
        });

        if ($this->getCacheKey() && $this->getCacheLifetime()) {
            $this->cache->save(
                $this->serializer->serialize($taxonomyData),
                $this->getCacheKey(),
                $this->getCacheTags(),
                $this->getCacheLifetime()
            );
        }

        $this->taxonomy = $taxonomyData;

        return $this->taxonomy;
    }

    /**
     * Get taxonomy data from the external URL and parse it into array
     *
     * @return array|bool
     */
    public function getTaxonomyData()
    {
        $curl = $this->curlFactory->create();
        $curl->setOptions(['timeout' => 15, 'header' => false]);

        try {
            $curl->write("GET", $this->getTaxonomyUrl(), '1.0');
            $response = $curl->read();
            $statusCode = (int) $curl->getInfo(CURLINFO_HTTP_CODE);
        } finally {
            $curl->close();
        }

        if ($response === '' || $response === false || $statusCode < 200 || $statusCode >= 300) {
            return false;
        }

        return $this->parseTaxonomy($response);
    }

    /**
     * Get taxonomy URL
     *
     * @return string
     */
    public function getTaxonomyUrl()
    {
        $locale = (string) $this->feed->getConfig('categories_locale');
        if (!preg_match('/^[a-z]{2}-[A-Z]{2}$/D', $locale)) {
            $locale = self::DEFAULT_LOCALE;
        }

        return sprintf(
            self::URL_FORMAT,
            $locale
        );
    }

    /**
     * Parse and filter taxonomy response
     *
     * @param string $response
     * @return array
     */
    protected function parseTaxonomy($response)
    {
        $taxonomyData = preg_split('/\r\n|\r|\n/', trim($response));

        return array_values(array_filter(array_map('trim', $taxonomyData), static function ($line) {
            return $line !== '' && strpos($line, '#') !== 0;
        }));
    }
}
