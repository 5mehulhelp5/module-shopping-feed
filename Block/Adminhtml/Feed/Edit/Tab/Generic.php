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

/**
 * Feed edit form General Configuration tab block
 */
namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab;

/**
 * Feed edit form Generic tab
 */
class Generic extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var string
     */
    protected $_template = 'feed/edit/tab.phtml';

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Converter
     */
    protected $feedConverter;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \MageOS\ShoppingFeed\Model\Feed\Converter $feedConverter
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \MageOS\ShoppingFeed\Model\Feed\Converter $feedConverter,
        array $data = []
    ) {
        $this->feedConverter = $feedConverter;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Check whether the tab can be shown
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check whether the tab is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param  string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get the form values: posted data kept after a failed save, else the feed's saved values
     *
     * @param \MageOS\ShoppingFeed\Model\Feed $model
     * @return mixed
     */
    protected function prepareValues($model)
    {
        $currentFeedData = $this->_coreRegistry->registry('current_feed');

        if (!empty($currentFeedData)) {
            return $currentFeedData;
        }

        return $this->feedConverter->createArrayFromObject($model);
    }
}
