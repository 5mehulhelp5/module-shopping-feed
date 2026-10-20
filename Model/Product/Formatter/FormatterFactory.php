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
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create (or reuse) the directive's formatter for the adapter's product type and bind the adapter to it.
     *
     * @param array<string,mixed> $directive
     * @param AdapterInterface $adapter
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
        $instanceName = $formatterData !== null
            ? $formatterData['type']
            : \MageOS\ShoppingFeed\Model\Product\Formatter\FormatterAbstract::class;

        if (!isset($this->objectCache[$instanceName])) {
            $this->objectCache[$instanceName] = $this->_objectManager->create($instanceName);
        }
        $this->objectCache[$instanceName]->setAdapter($adapter);

        return $this->objectCache[$instanceName];
    }

    /**
     * Returns null if no formatter specified, otherwise returns the formatter config array
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
        if ($parentProductType !== null) {
            $parentKey = sprintf('%s_child', $parentProductType);
        }

        if ($parentProductType !== null && isset($directive['formatters'][$parentKey])) {
            $data = $directive['formatters'][$parentKey];
        } elseif (isset($directive['formatters'][$productType])) {
            $data = $directive['formatters'][$productType];
        }
        return $data;
    }
}
