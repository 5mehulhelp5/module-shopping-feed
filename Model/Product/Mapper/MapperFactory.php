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

namespace MageOS\ShoppingFeed\Model\Product\Mapper;

use MageOS\ShoppingFeed\Model\Product\Adapter\AdapterInterface;

/**
 * Factory for Mapper classes. It holds cache of a mapper object and adds given adapter.
 *
 * Class MapperFactory
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper
 */
class MapperFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * @var array
     */
    protected $objectCache = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string                                    $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array                                                                           $directive
     * @param \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract|AdapterInterface $adapter
     *
     * @return \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract
     */
    public function create($directive, AdapterInterface $adapter)
    {
        $product = $adapter->getProduct();
        $productType = $product->getTypeId();
        $parentAdapterProductType = null;
        if ($adapter->hasParentAdapter()) {
            $parentAdapterProductType = $adapter->getParentAdapter()
                ->getProduct()
                ->getTypeId();
        }
        $mapperData = $this->getMapperData($directive, $productType, $parentAdapterProductType);
        $instanceName = $mapperData['type'];
        $configuration = $mapperData['configuration'];

        if (!isset($this->objectCache[$instanceName])) {
            $this->objectCache[$instanceName] = $this->_objectManager->create($instanceName);
            $this->objectCache[$instanceName]->resetConfiguration();
        }
        $this->objectCache[$instanceName]->addAdapter($adapter);

        foreach ($configuration as $path => $value) {
            $this->objectCache[$instanceName]->setConfiguration($path, $value);
        }

        return $this->objectCache[$instanceName];
    }

    public function getMapperData($directive, $productType, $parentProductType = null)
    {
        $mapper = $directive['mappers']['default'];

        $parentKey = null;
        if (!is_null($parentProductType)) {
            $parentKey = sprintf('%s_child', $parentProductType);
        }

        if (!is_null($parentProductType) && isset($directive['mappers'][$parentKey])) {
            $mapper = $directive['mappers'][$parentKey];
        } elseif (isset($directive['mappers'][$productType])) {
            $mapper = $directive['mappers'][$productType];
        }
        return $mapper;
    }
}
