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
 * Returns Average Product Review
 *
 * Class ProductReviewAverage
 *
 */
class ProductReviewAverage extends MapperAbstract
{
    /**
     * @var \Magento\Review\Model\Review\Summary
     */
    protected $summary;

    /**
     * ProductReviewAverage constructor.
     *
     * @param \Magento\Review\Model\Review\Summary $summary
     */
    public function __construct(
        \Magento\Review\Model\Review\Summary $summary
    ) {

        $this->summary = $summary;
    }

    /**
     * Average review rating on a five-point scale.
     *
     * @param  array $params
     * @return string
     */
    public function map(array $params = [])
    {
        if ($this->getAdapter()->hasParentAdapter()) {
            $product = $this->getAdapter()->getParentAdapter()->getProduct();
        } else {
            $product = $this->getAdapter()->getProduct();
        }

        $avg = 0;
        $summary = $this->summary->setData('store_id', $this->getAdapter()->getData('store_id'))
            ->load($product->getId());
        // rating_summary is a numeric DB column that Magento returns as a string.
        $ratingSummary = $summary->getRatingSummary();
        if (is_numeric($ratingSummary) && $ratingSummary > 0) {
            $avg = $ratingSummary * 5 / 100;
        }

        return $this->getAdapter()->getFilter()->cleanField($avg, $params);
    }
}
