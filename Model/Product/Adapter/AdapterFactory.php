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

namespace MageOS\ShoppingFeed\Model\Product\Adapter;

/**
 * Factory class for \MageOS\ShoppingFeed\Model\Product\Adapter\*
 */
class AdapterFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Created instances, keyed by product type
     *
     * @var array
     */
    protected $_instances = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param  \MageOS\ShoppingFeed\Model\Feed $feed
     * @param  \Magento\Catalog\Model\Product      $product
     * @return \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterInterface
     */
    public function create(\Magento\Catalog\Model\Product $product, \MageOS\ShoppingFeed\Model\Feed $feed, $singleton = true)
    {
        try {
            $productType = $product->getTypeId();

            if (!isset($this->_instances[$productType]) || !$singleton) {
                $className = sprintf('MageOS\ShoppingFeed\Model\Product\Adapter\Type\%s', ucfirst($productType));
                if (!class_exists($className)) {
                    //We switch to Simple adapter
                    $className = sprintf('MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple');
                }
                $object = $this->_objectManager->create(
                    $className, [
                    'product' => $product,
                    'feed'    => $feed
                    ]
                );
                if ($singleton) {
                    $this->_instances[$productType] = $object;
                } else {
                    return $object;
                }
            } else {
                $this->_instances[$productType]
                    ->unsetData()
                    ->setProduct($product)
                    ->setFeed($feed)
                    ->setAdapterData();
            }

            return $this->_instances[$productType];
        } catch (\Exception $e) {
            return false;
        }
    }
}
