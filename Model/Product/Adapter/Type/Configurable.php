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

namespace MageOS\ShoppingFeed\Model\Product\Adapter\Type;

use \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterInterface;

/**
 * Configurable Adapter, holds business logic between Product, Config and Mapper
 *
 * Class Configurable
 *
 * @package MageOS\ShoppingFeed\Model\Product\Adapter\Type
 */
class Configurable extends Composite implements AdapterInterface
{
    protected $productMetadata;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \MageOS\ShoppingFeed\Model\Feed $feed,
        \Magento\Catalog\Model\Product $product,
        \MageOS\ShoppingFeed\Model\FeedTypes\Config $feedTypesConfig,
        \MageOS\ShoppingFeed\Model\Product\Mapper\MapperFactory $mapperFactory,
        \MageOS\ShoppingFeed\Model\Product\Helper $helper,
        \Magento\Weee\Helper\Data $weeeData,
        \Magento\Tax\Helper\Data $taxData,
        \MageOS\ShoppingFeed\Model\Product\Helper\Catalog $catalogHelper,
        \Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price\CollectionFactory $catalogRuleCollectionFactory,
        \Magento\Catalog\Model\Product\Type\Price $productTypePrice,
        \Magento\CatalogInventory\Model\StockState $stockState,
        \MageOS\ShoppingFeed\Model\Product\Filter $filter,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Magento\Framework\Stdlib\DateTime $date,
        \MageOS\ShoppingFeed\Model\Product\Processors\OptionFactory $optionFactory,
        \MageOS\ShoppingFeed\Model\Generator\ProcessFactory $processFactory,
        \MageOS\ShoppingFeed\Model\ResourceModel\Generator\Process\CollectionFactory $processCollectionFactory,
        \MageOS\ShoppingFeed\Model\Logger $logger,
        \MageOS\ShoppingFeed\Model\Generator\Cache $cache,
        \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterFactory $adapterFactory,
        \MageOS\ShoppingFeed\Model\Product\Formatter\FormatterFactory $formatterFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \MageOS\ShoppingFeed\Model\Inventory\Api $sourceInventoryApi,
        \Magento\Framework\Serialize\SerializerInterface $jsonSerializer,
        \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Concatenate $concatenateMapper,
        array $data = []
    ) {

        $this->productMetadata = $productMetadata;
        parent::__construct(
            $filesystem, $feed, $product, $feedTypesConfig, $mapperFactory, $helper, $weeeData,
            $taxData, $catalogHelper, $catalogRuleCollectionFactory, $productTypePrice, $stockState, $filter,
            $localeResolver, $timezone, $date, $optionFactory, $processFactory, $processCollectionFactory, $logger,
            $cache, $adapterFactory, $formatterFactory, $categoryCollectionFactory, $sourceInventoryApi,
            $jsonSerializer, $concatenateMapper, $data
        );
    }

    /**
     * Creates an array of current configurable attributes/values
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getUrlOptions(\Magento\Catalog\Model\Product $product)
    {
        $params = [];
        if ($this->getFeed()->getConfig('configurable_associated_products_link_add_unique')) {
            /**
 * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProductType
*/
            $configurableProductType = $this->getProduct()->getTypeInstance();
            $codes = $configurableProductType->getConfigurableAttributes($this->getProduct());

            /**
 * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute
*/
            foreach ($codes as $attribute) {
                $eavAttribute = $attribute->getProductAttribute();
                $code = $eavAttribute->getAttributeCode();
                if (!$product->hasData($code)) {
                    continue;
                }
                $id = $attribute->getAttributeId();
                $value = $product->getData($code);

                if ($this->useAttributeCodeForUrl($attribute)) {
                    $params[$code] = $value;
                } else {
                    $params[$id] = $value;
                }
            }
        }

        return $params;
    }

    /**
     * @inheritdoc
     */
    public function beforeMap()
    {
        if (!$this->hasData('associated_product_adapters') || !is_array($this->getData('associated_product_adapters'))) {
            // Get associated products with this one
            $configurableProduct = $this->getProduct();
            /**
 * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection $associatedProductCollection
*/
            $associatedProductCollection = $configurableProduct->getTypeInstance()
                ->setStoreFilter($this->getStore(), $configurableProduct)
                ->getUsedProductCollection($configurableProduct)
                ->addAttributeToSelect('*');

            $associatedProductAdapters = $this->prepareAssociatedProductAdapters($associatedProductCollection);

            $this->setData('associated_product_adapters', $associatedProductAdapters);
        }

        return parent::beforeMap();
    }

    /**
     * @inheritdoc
     */
    public function getAssociatedProductsMode()
    {
        return $this->getFeed()->getConfig('configurable_associated_products_mode');
    }

    /**
     * @inheritdoc
     */
    public function getAssociatedMapInheritance()
    {
        return $this->getFeed()->getConfig('configurable_map_inherit', []);
    }

    public function useAttributeCodeForUrl($attribute)
    {
        /**
 * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attribute
*/
        $swatch = $attribute->getProductAttribute()->hasData('swatch_input_type');
        $mageVersion = $this->productMetadata->getVersion();

        return ($swatch && version_compare($mageVersion, '2.0.13', '>='));
    }
}
