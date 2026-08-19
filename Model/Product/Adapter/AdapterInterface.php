<?php

namespace MageOS\ShoppingFeed\Model\Product\Adapter;

/**
 * Interface for Adapters
 *
 * Interface AdapterInterface
 *
 * @package MageOS\ShoppingFeed\Model\Product\Adapter
 *
 * @method $this   setParentAdapter($this)
 */
interface AdapterInterface
{
    /**
     * Generates feed row(s) based on the given product
     * This is called for each Enabled & Visible (Catalog & Search) product
     *
     * @return array
     */
    public function map();

    /**
     * Gets value either from directive method or attribute method.
     *
     * @param  array $column
     * @return mixed
     */
    public function getMapValue(array $column = []);

    /**
     * Pull price & special price with & without tax
     *
     * @return array
     */
    public function getPrices();

    /**
     * @param  array $column
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|false
     */
    public function getMapAttribute($column = []);

    /**
     * @param  \Magento\Catalog\Model\Product      $product
     * @param  \Magento\Eav\Model\Entity\Attribute $attribute
     * @return string
     */
    public function getAttributeValue($product, $attribute);

    /**
     * @param  $args
     * @return mixed|string
     */
    public function mapEmptyValues($args);

    /**
     * Returns store instance
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore();

    /**
     * Returns filter instance
     *
     * @return \MageOS\ShoppingFeed\Model\Product\Filter
     */
    public function getFilter();

    /**
     * @return \MageOS\ShoppingFeed\Model\Feed
     */
    public function getFeed();

    /**
     * @return \Magento\Framework\Stdlib\DateTime\Timezone
     */
    public function getTimezone();

    /**
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct();

    /**
     * @return \MageOS\ShoppingFeed\Model\Product\Option
     */
    public function getOptionProcessor();

    /**
     * @return $this
     */
    public function setTestMode();

    /**
     * @return boolean
     */
    public function isTestMode();
}
