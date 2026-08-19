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

namespace MageOS\ShoppingFeed\Model\Product\Formatter;

use MageOS\ShoppingFeed\Model\Product\Adapter\AdapterInterface;

/**
 * Factory for Formatter classes. It holds cache of a mapper object and adds given adapter.
 *
 * Class MapperFactory
 *
 * @package MageOS\ShoppingFeed\Model\Product\Formatter
 */
class FormatterFactory
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
     * @param array $directive
     * @param \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract|AdapterInterface $adapter
     *
     * @return \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract
     */

    /**
     * @param  $directive
     * @param  AdapterInterface $adapter
     * @return mixed
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
        $formatterData = $this->getFormatterData($directive, $productType, $parentAdapterProductType);
        $instanceName = !is_null($formatterData) ? $formatterData['type'] : 'MageOS\ShoppingFeed\Model\Product\Formatter\FormatterAbstract';

        if (!isset($this->objectCache[$instanceName])) {
            $this->objectCache[$instanceName] = $this->_objectManager->create($instanceName);
        }
        $this->objectCache[$instanceName]->setAdapter($adapter);

        return $this->objectCache[$instanceName];
    }

    /**
     * Returns null if no formatter specified,
     * otherwise returns the formatter config array
     *
     * @param  array  $directive
     * @param  string $productType
     * @param  mixed  $parentProductType
     * @return mixed
     */
    public function getFormatterData($directive, $productType, $parentProductType = null)
    {
        if (!array_key_exists('formatters', $directive)) {
            return null;
        }

        $data = $directive['formatters']['default'];

        $parentKey = null;
        if (!is_null($parentProductType)) {
            $parentKey = sprintf('%s_child', $parentProductType);
        }

        if (!is_null($parentProductType) && isset($directive['formatters'][$parentKey])) {
            $data = $directive['formatters'][$parentKey];
        } elseif (isset($directive['formatters'][$productType])) {
            $data = $directive['formatters'][$productType];
        }
        return $data;
    }
}
