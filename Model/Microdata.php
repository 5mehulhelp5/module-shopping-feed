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

namespace MageOS\ShoppingFeed\Model;

use \Magento\Catalog\Model\Product;
use \Magento\Catalog\Model\Product\Option as Option;
use \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract as Adapter;
use \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\Availability;

class Microdata extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Adapter Factory instance
     *
     * @var \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterFactory
     */
    protected $adapterFactory;

    /**
     * Feed Factory instance.
     *
     * @var \MageOS\ShoppingFeed\Model\FeedFactory
     */
    protected $feedFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \MageOS\ShoppingFeed\Model\ResourceModel\Feed\CollectionFactory
     */
    protected $feedCollectionFactory;


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Config $eavConfig,
        \MageOS\ShoppingFeed\Model\FeedFactory $feedFactory,
        \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterFactory $adapterFactory,
        \MageOS\ShoppingFeed\Model\ResourceModel\Feed\CollectionFactory $feedCollectionFactory,
        array $data = []
    ) {

        parent::__construct($context, $registry, null, null, $data);
        $this->feedFactory = $feedFactory;
        $this->adapterFactory = $adapterFactory;
        $this->eavConfig = $eavConfig;
        $this->feedCollectionFactory = $feedCollectionFactory;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getMicrodata()
    {
        $row = $this->getRow();
        return $this->createRowObject($row);
    }

    /**
     * Retrieves all maps for current product - will include children maps if any
     *
     * @return array
     */
    public function getRow()
    {
        $product = $this->getProduct();
        $assocId = $this->getAssocId();

        /** @var \MageOS\ShoppingFeed\Model\Feed $feed */
        $feed = $this->getFeed();

        /** @var \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $adapter */
        $adapter = $this->adapterFactory->create($product, $feed);
        if (!$adapter) {
            return [];
        }

        if ($assocId !== false) {
            $parentMap = new \Magento\Framework\DataObject();
            $parentMap->setProduct($this->getBlockProduct());
            $adapter->setParentMap($parentMap);
        }

        // init associated_product_adapters
        $adapter->beforeMap();

        return $this->mapProduct($product, $adapter);
    }

    /**
     * Find the feed set to be used for microdata
     *
     * @return \MageOS\ShoppingFeed\Model\Feed
     */
    public function getFeed()
    {
        $store = $this->getStore();

        $feedLookup = $this->feedCollectionFactory->create()
            ->addFieldToFilter('store_id', $store->getId())
            ->addFieldToFilter('use_microdata', 1);

        if ($feedLookup->count()) {
            $feed = $feedLookup->getFirstItem();
        } else {
            $feed = $this->feedFactory->create()->load($store->getId(), 'store_id');
        }

        return $feed;
    }

    /**
     * Converts map array to microdata Object
     *
     * @param array $map map array returned by the generator
     * @return null|\Magento\Framework\DataObject
     */
    protected function createRowObject($map)
    {
        if (empty($map) || empty($map['price']) || empty($map['availability']) || empty($map['title'])) {
            return null;
        }

        $microdata = new \Magento\Framework\DataObject();
        $microdata->setName($map['title']);
        $microdata->setSku($map['sku']);

        if (!empty($map['sale_price'])) {
            $price = $map['sale_price'];
        } else {
            $price = $map['price'];
        }

        $microdata->setPrice(sprintf("%02.2f", $price));

        $microdata->setCurrency($map['currency']);

        if ($map['availability'] == Availability::IN_STOCK) {
            $microdata->setAvailability('https://schema.org/InStock');
        } else {
            $microdata->setAvailability('https://schema.org/OutOfStock');
        }

        if (array_key_exists('condition', $map)) {
            if (strcasecmp('new', $map['condition']) == 0) {
                $microdata->setCondition('https://schema.org/NewCondition');
            } elseif (strcasecmp('used', $map['condition']) == 0) {
                $microdata->setCondition('https://schema.org/UsedCondition');
            } elseif (strcasecmp('refurbished', $map['condition']) == 0) {
                $microdata->setCondition('https://schema.org/RefurbishedCondition');
            }
        }

        return $microdata;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $adapter
     * @param null $conditionAttribute
     * @return array
     */
    protected function mapProduct(Product $product, Adapter $adapter)
    {
        $columns_map = $adapter->getFeed()->getColumnsMap();
        $map = [
            'sku' => $product->getSku(), 'title' => $product->getName(),
            'price' => $product->getPrice(), 'sale_price' => $product->getSpecialPrice(),
            'availability' => $product->isAvailable() ? Availability::IN_STOCK : Availability::OUT_OF_STOCK,
            'condition' => 'new', 'currency' => $adapter->getFeed()->getConfig('general_currency')
        ];

        foreach($columns_map as $elm) {
            if (in_array($elm['column'], array_keys($map))) {
                $map[$elm['column']] = $adapter->getMapValue($elm);
            }
        }

        $optionPrice = $this->getOptionPrice($product);
        $map['price'] += (float)$optionPrice;
        if (!empty($map['sale_price'])) {
            $map['sale_price'] += $optionPrice;
        }

        return $map;
    }

    /**
     * @param Product $product
     * @return int
     */
    protected function getOptionPrice(Product $product)
    {
        $price = 0;
        $productOptions = $product->getOptions();

        if (is_array($productOptions) && count($productOptions) > 0) {
            // the request data will be passed to $this from the block
            $requestParams = $this->getRequestParams();

            /** @var Product\Option $option */
            foreach ($productOptions as $option) {
                $type = $option->getType();

                if ($type == Option::OPTION_TYPE_DROP_DOWN) {
                    $type = Option::OPTION_GROUP_SELECT;
                }

                $key = $type. '_'. $option->getId();
                if (array_key_exists($key, $requestParams)) {
                    $valueId = $requestParams[$key];

                    /** @var Product\Option\Value $values */
                    $values = $option->getValues();

                    foreach ($values as $value) {
                        if ($valueId == $value->getId()) {
                            $price += $value->getPrice(true);
                        }
                    }
                }
            }
        }

        return $price;
    }
}
