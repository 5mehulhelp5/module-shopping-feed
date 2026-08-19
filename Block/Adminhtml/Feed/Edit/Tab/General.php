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

use MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Element\DelimiterElement;

/**
 * Feed edit form General Configuration tab
 */
class General extends \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Directory\AvailableCurrencies
     */
    protected $currencies;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $sourceYesno;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\Attributes
     */
    protected $sourceAttributes;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Delimiter
     */
    protected $sourceDelimiter;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Encoding
     */
    protected $encodings;

    /**
     * General constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                                  $context
     * @param \Magento\Framework\Registry                                              $registry
     * @param \Magento\Framework\Data\FormFactory                                      $formFactory
     * @param \MageOS\ShoppingFeed\Model\Feed\Converter                            $feedConverter
     * @param \Magento\Store\Model\System\Store                                        $systemStore
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Directory\AvailableCurrencies $currencies
     * @param \Magento\Config\Model\Config\Source\Yesno                                $sourceYesno
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\Attributes            $sourceAttributes
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Delimiter                     $sourceDelimiter
     *
     * @param array                                                                    $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \MageOS\ShoppingFeed\Model\Feed\Converter $feedConverter,
        \Magento\Store\Model\System\Store $systemStore,
        \MageOS\ShoppingFeed\Model\Feed\Source\Directory\AvailableCurrencies $currencies,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\Attributes $sourceAttributes,
        \MageOS\ShoppingFeed\Model\Feed\Source\Delimiter $sourceDelimiter,
        \MageOS\ShoppingFeed\Model\Feed\Source\Encoding $encodings,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->currencies = $currencies;
        $this->sourceYesno = $sourceYesno;
        $this->sourceAttributes = $sourceAttributes;
        $this->sourceDelimiter = $sourceDelimiter;
        $this->encodings = $encodings;
        parent::__construct($context, $registry, $formFactory, $feedConverter, $data);
    }

    /**
     * Prepare form
     *
     * @return                                        $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        /* @var $model \MageOS\ShoppingFeed\Model\Feed */
        $model = $this->_coreRegistry->registry('feed');
        $values = $this->prepareValues($model);

        if ($this->_isAllowedAction('MageOS_ShoppingFeed::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('feed_');

        $fieldset = $form->addFieldset('feed_settings', ['legend' => __('Feed Settings')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        } else {
            $fieldset->addField('type', 'hidden', ['name' => 'type', 'value' => $model->getType()]);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'note' => __('The name of the Feed'),
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'select',
                [
                    'name' => 'store_id',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->systemStore->getStoreValuesForForm(false, false),
                    'disabled' => $isElementDisabled,
                    'note' => __('Specify from which store the feed will pull data.'),
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $id = $this->_storeManager->getDefaultStoreView()->getId();
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'store_id', 'value' => $id]
            );
            $model->setStoreId($id);
            $values['store_id'] = $id;
        }

        $fieldset->addField(
            'config_general_currency',
            'select',
            [
                'name' => 'config[general_currency]',
                'label' => __('Feed Currency'),
                'title' => __('Feed Currency'),
                'required' => true,
                'values' => $this->currencies->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('This lists only allowed currencies on the store view.<br />WARNING: Changing to a currency which is not displayed on frontend can lead to feed being rejected with provider!<br />Don\'t forget to update rates when adding new currency to the store.'),
            ]
        );

        $field = $fieldset->addField(
            'config_general_feed_dir',
            'select',
            [
                'name' => 'config[general_feed_dir]',
                'label' => __('Feed Path'),
                'title' => __('Feed Path'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'note' => __('It\'s the dir path to save the feed. Assure write permissions.'),
            ]
        );
        $renderer = $this->getLayout()->createBlock(
            \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Fieldset\Element::class
        );
        $renderer->setTemplate('feed/edit/tab/general/filepath.phtml');
        $renderer->setData('filename', array_key_exists('id', $values) ? sprintf($values['config_file_feed'], $values['id']) : $values['config_file_feed']);
        $field->setRenderer($renderer);

        $field = $fieldset->addField(
            'config_output_params_delimiter',
            'select',
            [
                'name' => 'output_params_delimiter',
                'label' => __('Delimiter'),
                'title' => __('Delimiter'),
                'required' => true,
                'values' => $this->sourceDelimiter->getOptionArray(),
                'disabled' => $isElementDisabled,
            ]
        );
        $renderer = $this->getLayout()->createBlock(
            \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Form\Fieldset\Element::class
        );

        $renderer->setTemplate('feed/edit/tab/general/delimiter.phtml');
        $renderer->setData('delimiter_other_value', array_key_exists('config_output_params_delimiter_other', $values) ? $values['config_output_params_delimiter_other'] : '');
        $field->setRenderer($renderer);

//        $fieldset->addField(
//            'config_output_params_encoding',
//            'select',
//            [
//                'name' => 'config[output_params_encoding]',
//                'label' => __('Output Encoding'),
//                'title' => __('Output Encoding'),
//                'required' => true,
//                'values' => $this->encodings->toOptionArray(),
//                'disabled' => $isElementDisabled,
//                'note' => __('Google requires UTF-8 by default, but you may customize this for other services.')
//            ]
//        );


        $fieldset = $form->addFieldset('general_configuration', ['legend' => __('General Configuration')]);

        $fieldset->addField(
            'config_general_apply_catalog_price_rules',
            'select',
            [
                'name' => 'config[general_apply_catalog_price_rules]',
                'label' => __('Apply Catalog Price Rules'),
                'title' => __('Apply Catalog Price Rules'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('It will apply catalog price rules when computing the Sale Price.'),
            ]
        );

        $fieldset->addField(
            'config_general_use_default_stock',
            'select',
            [
                'name' => 'config[general_use_default_stock]',
                'label' => __('Use default Stock Statuses'),
                'title' => __('Use default Stock Statuses'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('If your store is using a custom attribute for stock status, change this to No.'),
            ]
        );

        $fieldset->addField(
            'config_general_stock_attribute_code',
            'select',
            [
                'name' => 'config[general_stock_attribute_code]',
                'label' => __('Alternate Stock/Availability Attribute'),
                'title' => __('Alternate Stock/Availability Attribute'),
                'required' => false,
                'values' => $this->sourceAttributes->toOptionArray(true),
                'disabled' => $isElementDisabled,
                'note' => __('To fill \'availability\'. The attribute\'s values can be: \'in stock\', \'available for order\', \'out of stock\', \'preorder\'. Other values will be replaced by \'out of stock\'.'),
            ]
        );

        $fieldset->addField(
            'config_general_use_qty_increments',
            'select',
            [
                'name' => 'config[general_use_qty_increments]',
                'label' => __('Use Qty Increments'),
                'title' => __('Use Qty Increments'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('When computing product prices, use qty increments to multiply unit price'),
            ]
        );

        $fieldset->addField(
            'config_general_use_stock_reservations',
            'select',
            [
                'name' => 'config[general_use_stock_reservations]',
                'label' => __('Use Stock Reservations'),
                'title' => __('Use Stock Reservations'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Consider stock reservations when computing stock quantitys and availability'),
            ]
        );

        $fieldset->addField(
            'config_general_complex_duplicates_check',
            'select',
            [
                'name' => 'config[general_complex_duplicates_check]',
                'label' => __('Complex Product Context Prioritization'),
                'title' => __('Complex Product Context Prioritization'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Simple products "visible in catalog" are been prioritized to be processed in context of complex products if they are attached to configurable, grouped or bundle. It may slow down processing.'),
            ]
        );

        $this->_eventManager->dispatch(sprintf('mageos_shopping_feed_feed_edit_tab_general_prepare_form_%s', $model->getType()), [
            'form' => $form,
            'feed' => $model,
            'is_element_disabled' => $isElementDisabled,
        ]);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "feed_config_general_stock_attribute_code",
                'config[general_stock_attribute_code]'
            )->addFieldMap(
                "feed_config_general_use_default_stock",
                'config[general_use_default_stock]'
            )->addFieldDependence(
                'config[general_stock_attribute_code]',
                'config[general_use_default_stock]',
                0
            )
        );


        $form->setValues($values);
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
        return __('General Configuration');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('General Configuration');
    }
}
