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
 * Adminhtml ProductPrice directive options renderer
 */
class ProductPrice extends Template
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $sourceYesno;

    /**
     * @var string
     */
    protected $_template = 'feed/edit/form/options/renderer/product-price.phtml';

    /**
     * ProductPrice constructor.
     *
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param \Magento\Config\Model\Config\Source\Yesno $sourceYesno
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        array $data = []
    ) {
        $this->sourceYesno = $sourceYesno;
        parent::__construct($context, $data);
    }

    public function getYesNoOptions()
    {
        return $this->sourceYesno->toOptionArray();
    }
}
