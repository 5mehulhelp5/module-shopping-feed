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
 * Bundle Adapter, holds business logic between Product, Config and Mapper
 *
 * Class Bundle
 *
 * @package MageOS\ShoppingFeed\Model\Product\Adapter\Type
 */
class Bundle extends Composite implements AdapterInterface
{
    /**
     * @inheritdoc
     */
    public function beforeMap()
    {
        if (!$this->hasData('associated_product_adapters') || !is_array($this->getData('associated_product_adapters'))) {
            // Get associated products with this one
            $bundleProduct = $this->getProduct();
            /**
 * @var \Magento\Bundle\Model\Product\Type $bundleTypeInstance
*/
            $bundleTypeInstance = $bundleProduct->getTypeInstance();
            $associatedProductAdapters = [];

            $optionIds = $bundleTypeInstance->getOptionsIds($bundleProduct);
            if ($optionIds) {
                $bundleSelections = $bundleTypeInstance->getSelectionsCollection($optionIds, $bundleProduct);
                $bundleSelections = $bundleSelections->addAttributeToSelect('*');

                $associatedProductAdapters = $this->prepareAssociatedProductAdapters($bundleSelections);
            }

            $this->setData('associated_product_adapters', $associatedProductAdapters);
        }

        return parent::beforeMap();
    }

    /**
     * @inheritdoc
     */
    public function getAssociatedProductsMode()
    {
        return $this->getFeed()->getConfig('bundle_associated_products_mode');
    }

    /**
     * @inheritdoc
     */
    protected function getProductPrices(\Magento\Catalog\Model\Product $product)
    {
        /**
 * @var \MageOS\ShoppingFeed\Model\Product\Helper\Catalog $catalogHelper
*/
        $catalogHelper = $this->catalogHelper;

        $prices = [];
        $price = $product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
        $catalogRulesPrice = $this->getPriceByCatalogRules($price);
        $price = $catalogRulesPrice ? min($catalogRulesPrice, $price) : $price;
        $convertedPrice = $this->convertPrice($price);
        $prices['p_excl_tax'] = $catalogHelper->getTaxPrice($product, $convertedPrice);
        $prices['p_incl_tax'] = $catalogHelper->getTaxPrice($product, $convertedPrice, true);

        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
        $catalogRulesPrice = $this->getPriceByCatalogRules($finalPrice);
        $finalPrice = $catalogRulesPrice ? min($catalogRulesPrice, $finalPrice) : $finalPrice;
        $convertedFinalPrice = $this->convertPrice($finalPrice);
        $prices['sp_excl_tax'] = $catalogHelper->getTaxPrice($product, $convertedFinalPrice);
        $prices['sp_incl_tax'] = $catalogHelper->getTaxPrice($product, $convertedFinalPrice, true);

        return $prices;
    }

    /**
     * @param  bool|true $processRules
     * @param  null      $product
     * @return bool
     */
    public function hasSpecialPrice($processRules = true, $product = null)
    {
        $has = false;
        if (is_null($product)) {
            $product = $this->product;
        }

        if ($processRules && $this->hasPriceByCatalogRules()) {
            $has = true;
        } elseif ($this->helper->hasMsrp($product)) {
            $has = true;
        } else {
            $prices = $this->getProductPrices($product);
            $specialPrice = $prices['sp_incl_tax'];
            $price = $prices['p_incl_tax'];
            $locale = $this->localeResolver->getLocale();

            if ($specialPrice > 0 && $specialPrice < $price) {
                $cDate = $this->timezone->date(null, $locale);
                $dates = $this->getSpecialPriceEffectiveDates($product, false);
                /**
                 * @var \DateTime $start
                 * @var \DateTime $end
                 */
                extract($dates);

                if ($start <= $cDate && $end >= $cDate && ($specialPrice < $price || $price == 0)) {
                    $has = true;
                }
            }
        }

        return $has;
    }

    /**
     * Creates an array of current configurable attributes/values
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getUrlOptions(\Magento\Catalog\Model\Product $product)
    {
        return [];
    }
}
