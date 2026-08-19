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
 * Adminhtml ItemGroupId directive options renderer
 */
class ItemGroupId extends Template
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\ItemGroupAttributes
     */
    protected $sourceAttributes;

    /**
     * @var string
     */
    protected $_template = 'feed/edit/form/options/renderer/item-group-id.phtml';

    /**
     * ItemGroupId constructor.
     * @param Template\Context $context
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\ItemGroupAttributes $sourceAttributes
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\ItemGroupAttributes $sourceAttributes,
        array $data = []
    ) {

        $this->sourceAttributes = $sourceAttributes;
        parent::__construct($context, $data);
    }

    public function getAttributeOptions()
    {
        return $this->sourceAttributes->toOptionArray();
    }
}
