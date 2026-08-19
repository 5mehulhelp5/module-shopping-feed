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

namespace MageOS\ShoppingFeed\Model\Product\Helper;

use Magento\Tax\Api\Data\TaxClassKeyInterface;

/**
 * Catalog helper, overrides getTaxPrice method to push calculating taxes despite on config
 *
 * Class Catalog
 *
 * @package MageOS\ShoppingFeed\Model\Catalog\Helper
 */
class Catalog extends \Magento\Catalog\Helper\Data
{
    /**
     * Get product price with all tax settings processing
     * Overrides parent method to push calculating taxes despite on config if $includingTax = true
     *
     * @author yurii.pochtovyk@rocketweb.com
     *
     * @param  \Magento\Catalog\Model\Product                       $product
     * @param  float                                                $price            inputted product price
     * @param  bool                                                 $includingTax     return price include tax flag
     * @param  null|\Magento\Customer\Model\Address\AbstractAddress $shippingAddress
     * @param  null|\Magento\Customer\Model\Address\AbstractAddress $billingAddress
     * @param  null|int                                             $ctc              customer tax class
     * @param  null|string|bool|int|\Magento\Store\Model\Store      $store
     * @param  bool                                                 $priceIncludesTax flag what price parameter contain tax
     * @param  bool                                                 $roundPrice
     * @return float
     */
    public function getTaxPrice(
        $product,
        $price,
        $includingTax = null,
        $shippingAddress = null,
        $billingAddress = null,
        $ctc = null,
        $store = null,
        $priceIncludesTax = null,
        $roundPrice = true
    ) {
        // If tax has to be included call new method to oush price calculation
        if ($includingTax == true) {
            return $this->getTaxPriceIncludingTax(
                $product,
                $price,
                $includingTax,
                $ctc,
                $store,
                $priceIncludesTax,
                $roundPrice
            );
        }
        // If tax is not required use native method
        return parent::getTaxPrice(
            $product,
            $price,
            $includingTax,
            $shippingAddress,
            $billingAddress,
            $ctc,
            $store,
            $priceIncludesTax,
            $roundPrice
        );
    }

    /**
     * Get product price with all tax settings processing
     * Always calculating taxes despite on config
     *
     * @param                                         \Magento\Catalog\Model\Product                       $product
     * @param                                         float                                                $price            inputted product price
     * @param                                         bool                                                 $includingTax     return price include tax flag
     * @param                                         null|\Magento\Customer\Model\Address\AbstractAddress $shippingAddress
     * @param                                         null|\Magento\Customer\Model\Address\AbstractAddress $billingAddress
     * @param                                         null|int                                             $ctc              customer tax class
     * @param                                         null|string|bool|int|\Magento\Store\Model\Store      $store
     * @param                                         bool                                                 $priceIncludesTax flag what price parameter contain tax
     * @param                                         bool                                                 $roundPrice
     * @return                                        float
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getTaxPriceIncludingTax(
        $product,
        $price,
        $includingTax = null,
        $ctc = null,
        $store = null,
        $priceIncludesTax = null,
        $roundPrice = true
    ) {
        if (!$price) {
            return $price;
        }

        $store = $this->_storeManager->getStore($store);

        /**
 * Do not check store configuration here, always calculate taxes
*/
        /**
 * @author yurii.pochtovyk@rocketweb.com
*/

        if ($priceIncludesTax === null) {
            $priceIncludesTax = $this->_taxConfig->priceIncludesTax($store);
        }

        /**
 * Setting address objects null since there are no chances to have it
*/
        $shippingAddressDataObject = null;
        $billingAddressDataObject = null;

        $taxClassKey = $this->_taxClassKeyFactory->create();
        $taxClassKey->setType(TaxClassKeyInterface::TYPE_ID)
            ->setValue($product->getTaxClassId());

        if ($ctc === null && $this->_customerSession->getCustomerGroupId() != null) {
            $ctc = $this->customerGroupRepository->getById($this->_customerSession->getCustomerGroupId())
                ->getTaxClassId();
        }

        $customerTaxClassKey = $this->_taxClassKeyFactory->create();
        $customerTaxClassKey->setType(TaxClassKeyInterface::TYPE_ID)
            ->setValue($ctc);

        $item = $this->_quoteDetailsItemFactory->create();
        $item->setQuantity(1)
            ->setCode($product->getSku())
            ->setShortDescription($product->getShortDescription())
            ->setTaxClassKey($taxClassKey)
            ->setIsTaxIncluded($priceIncludesTax)
            ->setType('product')
            ->setUnitPrice($price);

        $quoteDetails = $this->_quoteDetailsFactory->create();
        $quoteDetails->setShippingAddress($shippingAddressDataObject)
            ->setBillingAddress($billingAddressDataObject)
            ->setCustomerTaxClassKey($customerTaxClassKey)
            ->setItems([$item])
            ->setCustomerId($this->_customerSession->getCustomerId());

        $storeId = null;
        if ($store) {
            $storeId = $store->getId();
        }
        $taxDetails = $this->_taxCalculationService->calculateTax($quoteDetails, $storeId, $roundPrice);
        $items = $taxDetails->getItems();
        $taxDetailsItem = array_shift($items);

        if ($includingTax !== null) {
            if ($includingTax) {
                $price = $taxDetailsItem->getPriceInclTax();
            } else {
                $price = $taxDetailsItem->getPrice();
            }
        } else {
            switch ($this->_taxConfig->getPriceDisplayType($store)) {
            case Config::DISPLAY_TYPE_EXCLUDING_TAX:
            case Config::DISPLAY_TYPE_BOTH:
                $price = $taxDetailsItem->getPrice();
                break;
            case Config::DISPLAY_TYPE_INCLUDING_TAX:
                $price = $taxDetailsItem->getPriceInclTax();
                break;
            default:
                break;
            }
        }

        if ($roundPrice) {
            return $this->priceCurrency->round($price);
        } else {
            return $price;
        }
    }
}
