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

namespace MageOS\ShoppingFeed\Model\Product\Processors;

class ProcessorAbstract
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract
     */
    protected $adapter;

    /**
     * @param  \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $adapter
     * @return $this
     */
    public function setAdapter(\MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return \MageOS\ShoppingFeed\Model\Product\Adapter\AdapterAbstract
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param  array $rows
     * @return array
     */
    public function execute(array $rows)
    {
        return $rows;
    }
}
