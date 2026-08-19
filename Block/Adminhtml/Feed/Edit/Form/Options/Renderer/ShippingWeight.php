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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Options\Renderer;

use Magento\Framework\View\Element\Template;

/**
 * Adminhtml ShippingWeight directive options renderer
 */
class ShippingWeight extends Template
{
    protected $sourceWeight;

    /**
     * @var string
     */
    protected $_template = 'feed/edit/form/options/renderer/shipping-weight.phtml';

    /**
     * ShippingWeight constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                    $context
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Shipping\Weight $sourceWeight
     * @param array                                                      $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \MageOS\ShoppingFeed\Model\Feed\Source\Shipping\Weight $sourceWeight,
        array $data = []
    ) {
        $this->sourceWeight = $sourceWeight;
        parent::__construct($context, $data);
    }

    /**
     * Shipping Weight suffixes options
     *
     * @return array
     */
    public function getWeightOptions()
    {
        return $this->sourceWeight->toOptionArray();
    }
}
