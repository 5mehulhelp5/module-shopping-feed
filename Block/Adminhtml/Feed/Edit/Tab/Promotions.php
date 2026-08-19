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

/**
 * Feed edit form Categories Map tab block
 */
class Promotions extends \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'feed/edit/tab/promotions.phtml';

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $sourceYesno;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \MageOS\ShoppingFeed\Model\Feed\Converter $feedConverter
     * @param \Magento\Config\Model\Config\Source\Yesno $sourceYesno
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \MageOS\ShoppingFeed\Model\Feed\Converter $feedConverter,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        array $data = []
    ) {
        $this->sourceYesno = $sourceYesno;

        parent::__construct($context, $registry, $formFactory, $feedConverter, $data);
    }

    /**
     * Show the tab only on google shopping feed type.
     *
     * @return bool
     */
    public function canShowTab()
    {
        /* @var $model \MageOS\ShoppingFeed\Model\Feed */
        $feed = $this->_coreRegistry->registry('feed');
        return ($feed->getData('type') == 'google_shopping');
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        /* @var $model \MageOS\ShoppingFeed\Model\Feed */
        $model = $this->_coreRegistry->registry('feed');

        if ($this->_isAllowedAction('MageOS_ShoppingFeed::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('feed_');

        $fieldset = $form->addFieldset('google_promotions', ['legend' => __('Google Promotions')]);


        $fieldset->addField(
            'config_promotions_enabled',
            'select',
            [
                'name' => 'config[promotions_enabled]',
                'label' => __('Enable feed'),
                'title' => __('Enabled'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Turns On/Off generation of google promotions feed.'),
            ]
        );

        $field = $fieldset->addField(
            'config_promotions_provider_widget',
            'text',
            [
                'name' => 'config[promotions_provider_widget]',
                'label' => '',
                'title' => __('Promotions'),
                'required' => false,
                'disabled' => $isElementDisabled,
            ]
        );

        $renderer = $this->getLayout()->createBlock(
            'MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Promotions\Widget'
        );
        $field->setRenderer($renderer);

        $this->_eventManager->dispatch(sprintf('mageos_shopping_feed_feed_edit_tab_promotions_prepare_form_%s', $model->getType()), [
            'form' => $form,
            'feed' => $model,
            'is_element_disabled' => $isElementDisabled,
        ]);

        $form->setValues($this->prepareValues($model));
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Google Promotions');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Google Promotions');
    }

    /**
     * Prepare tab notice
     *
     * @return string
     */
    public function getTabNotice()
    {
        return __('Defines google promotions feed. When enabled, the promotions ids generated with this feed would also be listed in products feed through <strong>Promotions IDs</strong> directive. Make sure you have a column promotion_id defined in <strong>Columns Map</strong> with this directive.');
    }
}
