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

namespace MageOS\ShoppingFeed\Model\Feed\Source\Product;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\Product\Type as ProductType;

/**
 * Class Columns
 */
class Columns implements OptionSourceInterface
{
    /**
     * Columns cache
     *
     * @var array
     */
    protected $columns;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Types constructor.
     *
     * @param ProductType $productType
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = [
            'label' => '',
            'value' => null
        ];
        foreach ($this->getColumns() as $column) {
            $options[] = [
                'label' => $column['column'],
                'value' => $column['column'],
            ];
        }

        return $options;
    }

    /**
     * Retrieve columns
     *
     * @return array
     */
    public function getColumns()
    {
        if ($this->columns !== null) {
            return $this->columns;
        }

        /* @var $model \MageOS\ShoppingFeed\Model\Feed */
        $feed = $this->coreRegistry->registry('feed');

        if ($feed->getConfig()) {
            $this->columns = $feed->getConfig()->getData('columns_product_columns');
        }

        return $this->columns;
    }
}
