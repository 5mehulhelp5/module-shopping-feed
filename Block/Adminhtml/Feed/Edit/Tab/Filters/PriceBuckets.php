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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Filters;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Element\AbstractArrayElement;

/**
 * Adminhtml price buckets renderer
 */
class PriceBuckets extends AbstractArrayElement implements RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'feed/edit/tab/filters/price-buckets.phtml';

    /**
     * Sort price bucket values callback method
     *
     * @param array $a
     * @param array $b
     * @return int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function sortValuesCallback($a, $b)
    {
        if ($a['pricefrom'] != $b['pricefrom']) {
            return $a['pricefrom'] < $b['pricefrom'] ? -1 : 1;
        }

        return 0;
    }

    /**
     * Retrieve 'Add Price Range' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label' => __('Add Price Range'),
                'onclick' => 'return priceBucketsControl.addItem()',
                'class' => 'add'
            ]
        );
        $button->setName('add_price_range_button');

        $this->setChild('add_button', $button);
        return $this->getChildHtml('add_button');
    }
}
