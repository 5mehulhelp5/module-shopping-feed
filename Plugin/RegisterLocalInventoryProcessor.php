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
 * @copyright Copyright (c) 2023 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */

namespace MageOS\ShoppingFeed\Plugin;

class RegisterLocalInventoryProcessor
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Processors\LocalInventoryFactory
     */
    protected $processorFactory;

    public function __construct(
        \MageOS\ShoppingFeed\Model\Product\Processors\LocalInventoryFactory $processorFactory
    ) {
        $this->processorFactory = $processorFactory;
    }

    public function afterGetAdditionalProcessors(\MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $subject, $result) {
        if ($subject->getFeed()->getType() == 'google_local_inventory') {
            array_push($result, $this->processorFactory->create());
        }
        return $result;
    }
}