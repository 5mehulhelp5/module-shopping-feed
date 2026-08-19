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
 * Feed edit form Configurable Products tab block
 */
namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab;

/**
 * Feed edit form Configurable Products tab
 */
class Configurable extends \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode
     */
    protected $sourceAssociatedMode;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $sourceYesno;

    /**
     * @param \Magento\Backend\Block\Template\Context                           $context
     * @param \Magento\Framework\Registry                                       $registry
     * @param \Magento\Framework\Data\FormFactory                               $formFactory
     * @param \MageOS\ShoppingFeed\Model\Feed\Converter                     $feedConverter
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode $sourceAssociatedMode
     * @param \Magento\Config\Model\Config\Source\Yesno                         $sourceYesno
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns        $sourceProductColumns
     * @param array                                                             $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \MageOS\ShoppingFeed\Model\Feed\Converter $feedConverter,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode $sourceAssociatedMode,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        array $data = []
    ) {
        $this->sourceAssociatedMode = $sourceAssociatedMode;
        $this->sourceYesno = $sourceYesno;
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

        if ($this->_isAllowedAction('MageOS_ShoppingFeed::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /**
 * @var \Magento\Framework\Data\Form $form
*/
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('feed_');

        $fieldset = $form->addFieldset('feed_configurable', ['legend' => __('Configurable Products')]);

        $fieldset->addField(
            'config_configurable_associated_products_mode',
            'select',
            [
                'name' => 'config[configurable_associated_products_mode]',
                'label' => __('How to add associated products'),
                'title' => __('How to add associated products'),
                'required' => true,
                'values' => $this->sourceAssociatedMode->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Associated products can be added in the feed as separate items even if they are not visible in catalog.'),
            ]
        );

        $fieldset->addField(
            'config_configurable_add_out_of_stock',
            'select',
            [
                'name' => 'config[configurable_add_out_of_stock]',
                'label' => __('Allow Out of Stock'),
                'title' => __('Allow Out of Stock'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('For associated products of configurable products.'),
            ]
        );

        $fieldset->addField(
            'config_configurable_inherit_parent_out_of_stock',
            'select',
            [
                'name' => 'config[configurable_inherit_parent_out_of_stock]',
                'label' => __('Inherit parent Out of Stock status'),
                'title' => __('Inherit parent Out of Stock status'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Forces "Out of Stock" for all sub-items when the configurable item is Out of Stock.'),
            ]
        );

        $fieldset->addField(
            'config_configurable_associated_products_link_add_unique',
            'select',
            [
                'name' => 'config[configurable_associated_products_link_add_unique]',
                'label' => __('Unique urls for associated products not visible'),
                'title' => __('Unique urls for associated products not visible'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('The new unique url will be formed from configurable product url and the option ids as parameters. e.g http://example.com/configurable.html?option_1=x&option2=y'),
            ]
        );

        $fieldset->addField(
            'config_configurable_attribute_merge_value_separator',
            'text',
            [
                'name' => 'config[configurable_attribute_merge_value_separator]',
                'label' => __('Associated Product Attribute Value Separator'),
                'title' => __('Associated Product Attribute Value Separator'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'note' => __('Variant attributes values like color and size, defined above, are been merged together for each item using the separator defined here.'),
            ]
        );

        $field = $fieldset->addField(
            'config_configurable_map_inherit',
            'text',
            [
                'name' => 'config[configurable_map_inherit]',
                'label' => __('Value inheritance by column'),
                'title' => __('Value inheritance by column'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'note' => __('Define columns which sould grab value from parent or associated item.')
            ]
        );

        $renderer = $this->getLayout()->createBlock(
            'MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Complex\Inherit'
        );
        $field->setRenderer($renderer);

        $this->_eventManager->dispatch(
            sprintf('mageos_shopping_feed_feed_edit_tab_configurable_prepare_form_%s', $model->getType()), [
            'form' => $form,
            'feed' => $model,
            'is_element_disabled' => $isElementDisabled,
            ]
        );

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
        return __('Configurable Products');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Configurable Products');
    }

    /**
     * Prepare tab notice
     *
     * @return string
     */
    public function getTabNotice()
    {
        return __('This section applyes to all configurable and their associated produts in your catalog. Configurable type should be enabled under <a href="#" data-tab-id="#feed_tabs_filters">Filters</a> section.');
    }
}
