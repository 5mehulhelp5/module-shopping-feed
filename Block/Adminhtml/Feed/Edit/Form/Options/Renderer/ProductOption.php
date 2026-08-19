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
 * Adminhtml ProductOption directive options renderer
 */
class ProductOption extends Template
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\Options
     */
    protected $sourceOptions;

    /**
     * @var string
     */
    protected $_template = 'feed/edit/form/options/renderer/product-option.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\Options $sourceOptions,
        array $data = []
    ) {
        $this->sourceOptions = $sourceOptions;
        parent::__construct($context, $data);
    }

    public function getProductOptions()
    {
        return $this->sourceOptions->toOptionArray();
    }
}
