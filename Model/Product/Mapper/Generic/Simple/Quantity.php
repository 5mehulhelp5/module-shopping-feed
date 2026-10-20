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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

/**
 * Returns Product Stock Quantity
 *
 * Class Quantity
 *
 */
class Quantity extends MapperAbstract
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Inventory\Api
     */
    protected $sourceInventoryApi;

    /**
     * Quantity mapper constructor.
     *
     * @param \MageOS\ShoppingFeed\Model\Logger $logger
     * @param \MageOS\ShoppingFeed\Model\Inventory\Api $sourceInventoryApi
     */
    public function __construct(
        \MageOS\ShoppingFeed\Model\Logger $logger,
        \MageOS\ShoppingFeed\Model\Inventory\Api $sourceInventoryApi
    ) {
        $this->sourceInventoryApi = $sourceInventoryApi;
        parent::__construct($logger);
    }

    /**
     * Saleable stock quantity.
     *
     * @param  array $params
     * @return string
     */
    public function map(array $params = [])
    {
        return $this->getAdapter()->getFilter()->cleanField(
            sprintf('%d', $this->getAdapter()->getInventoryCount()),
            $params
        );
    }
}
