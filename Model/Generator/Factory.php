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

namespace MageOS\ShoppingFeed\Model\Generator;

/**
 * Factory for Mapper classes. It holds cache of a mapper object and adds given adapter.
 *
 * Class MapperFactory
 *
 * @package MageOS\ShoppingFeed\Model\Product\Mapper
 */
class Factory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

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
     * @param  \MageOS\ShoppingFeed\Model\Generator\Queue $queue
     * @param  \MageOS\ShoppingFeed\Model\Feed|int|null   $feed
     * @param  string                                         $instanceName
     * @return mixed
     */
    public function create($feed, $queue = null, $testSku = null, $instanceName = 'MageOS\ShoppingFeed\Model\Generator')
    {
        $parameters = ['feed' => $feed];
        if (!is_null($queue)) {
            $parameters['queue'] = $queue;
        }

        if (!is_null($testSku)) {
            $parameters['testSku'] = $testSku;
        }
        return $this->_objectManager->create($instanceName, $parameters);
    }
}
