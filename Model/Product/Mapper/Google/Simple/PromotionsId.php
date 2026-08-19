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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Google\Simple;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

class PromotionsId extends MapperAbstract
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Promotions\Provider
     */
    protected $promotionsProvider;

    public function __construct(
        \MageOS\ShoppingFeed\Model\Logger $logger,
        \MageOS\ShoppingFeed\Model\Promotions\Provider $promotionsProvider
    ) {

        $this->promotionsProvider = $promotionsProvider;
        parent::__construct($logger);
    }

    public function map(array $params = [])
    {
        $this->promotionsProvider->setFeed($this->getAdapter()->getFeed());
        $promotionIds = $this->promotionsProvider->getPromotionIds($this->getAdapter()->getProduct());

        return implode(',', $promotionIds);
    }
}
