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
 * Grouped Adapter, holds business logic between Product, Config and Mapper
 *
 * Class Grouped
 *
 * @package MageOS\ShoppingFeed\Model\Product\Adapter\Type
 */
class Grouped extends Composite implements AdapterInterface
{
    /**
     * Adds prod_id param to URL
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getUrlOptions(\Magento\Catalog\Model\Product $product)
    {
        $params = [];
        if ($this->getFeed()->getConfig('grouped_associated_products_link_add_unique')) {
            $params['prod_id'] = $product->getId();
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
            $groupedProduct = $this->getProduct();

            /**
 * @var \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $associatedProductCollection
*/
            $associatedProductCollection = $groupedProduct->getTypeInstance()->getAssociatedProductCollection($groupedProduct)
                ->addAttributeToSelect('*')
                ->addFilterByRequiredOptions()
                ->setPositionOrder()
                ->addStoreFilter($this->getStore());

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
        return $this->getFeed()->getConfig('grouped_associated_products_mode');
    }

    /**
     * @inheritdoc
     */
    public function getAssociatedMapInheritance()
    {
        return $this->getFeed()->getConfig('grouped_map_inherit', []);
    }
}
