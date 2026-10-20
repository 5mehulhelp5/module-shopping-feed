<?php

namespace MageOS\ShoppingFeed\Model\Product\Adapter;

/**
 * Interface for Adapters
 *
 * Interface AdapterInterface
 *
 *
 * Implementations extend AdapterAbstract, a DataObject, which provides these accessors.
 *
 * @method $this setParentAdapter(AdapterInterface $parentAdapter)
 * @method AdapterInterface|null getParentAdapter()
 * @method bool hasParentAdapter()
 * @method $this setData(string|array $key, mixed $value = null)
 */
interface AdapterInterface
{
    /**
     * Generates feed row(s) based on the given product
     *
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
     * Resolve the product attribute a column map entry reads.
     *
     * @param  array|string $column Column map entry, or a bare attribute code
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|false
     */
    public function getMapAttribute($column = []);

    /**
     * Product attribute value as feed text: Yes/No for booleans, option labels for selects.
     *
     * @param  \Magento\Catalog\Model\Product                        $product
     * @param  \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return string
     */
    public function getAttributeValue($product, $attribute);

    /**
     * Value from the first matching "replace empty column" rule for the given column.
     *
     * @param array<string,mixed> $args Column map entry
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
     * Feed being generated.
     *
     * @return \MageOS\ShoppingFeed\Model\Feed
     */
    public function getFeed();

    /**
     * Timezone used for price effective dates.
     *
     * @return \Magento\Framework\Stdlib\DateTime\Timezone
     */
    public function getTimezone();

    /**
     * Product being mapped.
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct();

    /**
     * Product options processor bound to this adapter.
     *
     * @return \MageOS\ShoppingFeed\Model\Product\Processors\Option
     */
    public function getOptionProcessor();

    /**
     * Enable test mode, which skips the duplicate-product check.
     *
     * @return $this
     */
    public function setTestMode();

    /**
     * Whether the adapter runs in test mode.
     *
     * @return boolean
     */
    public function isTestMode();
}
