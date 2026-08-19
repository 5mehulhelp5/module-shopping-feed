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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddGoogleGeneralMicrodataFieldsObserver implements ObserverInterface
{
    /**
     * Parent layout of the block
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $sourceYesno;


    /**
     * @param \Magento\Framework\View\Element\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno
    ) {
        $this->layout = $context->getLayout();
        $this->sourceYesno = $sourceYesno;
    }

    /**
     * Adds Google Shopping fields to Filters tab feed edit page
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $feed = $observer->getEvent()->getFeed();
        $form = $observer->getEvent()->getForm();
        $isElementDisabled = $observer->getEvent()->getIsElementDisabled();

        if ($form->getElement('general_configuration') && $feed->getType() == 'google_shopping') {
            $fieldset = $form->getElement('general_configuration');
            $fieldset->addField(
                'use_microdata',
                'select',
                [
                    'name' => 'use_microdata',
                    'label' => __('Use for microdata'),
                    'title' => __('Use for microdata'),
                    'required' => true,
                    'values' => $this->sourceYesno->toOptionArray(),
                    'disabled' => $isElementDisabled,
                    'note' => __('When set to yes, store\'s microdata uses mapping of this feed.
                    Only one feed per store should have this turned on.'),
                ]
            );
        }
    }
}
