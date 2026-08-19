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
 * Feed edit block
 */
namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Initialize feed edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'MageOS_ShoppingFeed';
        $this->_controller = 'adminhtml_feed';

        parent::_construct();

        $this->buttonList->remove('reset');
        $this->buttonList->add(
            'test',
            ['label' => __('Test Feed'), 'onclick' => "window.open('". $this->getTestFeedUrl(). "','feed_test','width=835,height=700,resizable=1,scrollbars=1');"]
        );

        if ($this->_isAllowedAction('MageOS_ShoppingFeed::save')) {
            $this->buttonList->update('save', 'label', __('Save Page'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -2
            );
        } else {
            $this->buttonList->remove('save');
        }
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
     * @return string
     */
    public function getTestFeedUrl()
    {
        return $this->getUrl('*/*/test', [$this->_objectId => $this->getRequest()->getParam($this->_objectId)]);
    }
}
