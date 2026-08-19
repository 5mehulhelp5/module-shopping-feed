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
 * Adminhtml ProductImage directive options renderer
 */
class ProductImage extends Template
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\ImageAttributes
     */
    protected $sourceImageAttributes;

    /**
     * @var string
     */
    protected $_template = 'feed/edit/form/options/renderer/product-image.phtml';

    /**
     * ProductImage constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                            $context
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\ImageAttributes $sourceImageAttributes
     * @param array                                                              $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\ImageAttributes $sourceImageAttributes,
        array $data = []
    ) {
        $this->sourceImageAttributes = $sourceImageAttributes;
        parent::__construct($context, $data);
    }

    public function getImageAttributes()
    {
        return $this->sourceImageAttributes->toOptionArray();
    }
}
