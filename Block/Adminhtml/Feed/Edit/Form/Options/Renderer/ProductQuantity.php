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
 * Adminhtml ProductQuantity directive options renderer
 */
class ProductQuantity extends Template
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\QuantityHandling
     */
    protected $sourceQuantityHandling;

    /**
     * @var string
     */
    protected $_template = 'feed/edit/form/options/renderer/product-quantity.phtml';

    /**
     * ProductQuantity constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                             $context
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\QuantityHandling $sourceQuantityHandling
     * @param array                                                               $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\QuantityHandling $sourceQuantityHandling,
        array $data = []
    ) {
        $this->sourceQuantityHandling = $sourceQuantityHandling;
        parent::__construct($context, $data);
    }

    public function getQuantityHandlingOptions()
    {
        return $this->sourceQuantityHandling->toOptionArray();
    }
}
