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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed;

class Test extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * Initialize feed test block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'MageOS_ShoppingFeed';
        $this->_controller = 'adminhtml_feed';
        $this->_mode = 'test';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('back');
        $this->buttonList->add(
            'close',
            [
                'label' => __('Close'),
                'class' => 'reset',
                'onclick' => 'window.close()'
            ],
            -1
        );
        $this->buttonList->add(
            'test',
            [
                'label' => __('Test Now'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'submit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            0
        );
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/test');
    }
}
