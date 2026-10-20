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
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create a generator for the feed, optionally bound to a queue item or a single test SKU.
     *
     * @param \MageOS\ShoppingFeed\Model\Feed|int|null $feed
     * @param \MageOS\ShoppingFeed\Model\Generator\Queue|null $queue
     * @param string|null $testSku
     * @param string $instanceName
     * @return mixed
     */
    public function create(
        $feed,
        $queue = null,
        $testSku = null,
        $instanceName = \MageOS\ShoppingFeed\Model\Generator::class
    ) {
        $parameters = ['feed' => $feed];
        if ($queue !== null) {
            $parameters['queue'] = $queue;
        }

        if ($testSku !== null) {
            $parameters['testSku'] = $testSku;
        }
        return $this->_objectManager->create($instanceName, $parameters);
    }
}
