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
 * Feed edit form Product Filters tab block
 */
namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab;

/**
 * Feed edit form Product Filters tab
 */
class Filters extends \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $sourceYesno;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\Types
     */
    protected $sourceProductTypes;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\AttributeSets
     */
    protected $attributeSetOptions;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns
     */
    protected $sourceProductColumns;

    /**
     * Filters constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                          $context
     * @param \Magento\Framework\Registry                                      $registry
     * @param \Magento\Framework\Data\FormFactory                              $formFactory
     * @param \MageOS\ShoppingFeed\Model\Feed\Converter                    $feedConverter
     * @param \Magento\Config\Model\Config\Source\Yesno                        $sourceYesno
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\Types         $sourceProductTypes
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\AttributeSets $attributeSetOptions
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns       $sourceProductColumns
     * @param array                                                            $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \MageOS\ShoppingFeed\Model\Feed\Converter $feedConverter,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\Types $sourceProductTypes,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\AttributeSets $attributeSetOptions,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns $sourceProductColumns,
        array $data = []
    ) {
        $this->sourceYesno = $sourceYesno;
        $this->sourceProductTypes = $sourceProductTypes;
        $this->attributeSetOptions = $attributeSetOptions;
        $this->sourceProductColumns = $sourceProductColumns;
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

        $fieldset = $form->addFieldset('feed_filters', ['legend' => __('Product Filters')]);

        $fieldset->addField(
            'config_filters_add_out_of_stock',
            'select',
            [
                'name' => 'config[filters_add_out_of_stock]',
                'label' => __('Allow Out of Stock'),
                'title' => __('Allow Out of Stock'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
            ]
        );

        $fieldset->addField(
            'config_filters_product_types',
            'multiselect',
            [
                'name' => 'config[filters_product_types]',
                'label' => __('Submit only products of these types'),
                'title' => __('Submit only products of these types'),
                'required' => true,
                'values' => $this->sourceProductTypes->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Products submitted to the feed have to be visible in Catalog, meaning visibility "Catalog", "Search", "Catalog, Search", but also "Not Visible Individually if they are part of a visible configurable".'),
            ]
        );

        $fieldset->addField(
            'config_filters_attribute_sets',
            'multiselect',
            [
                'name' => 'config[filters_attribute_sets]',
                'label' => __('Submit only products that have these attribute sets'),
                'title' => __('Submit only products that have these attribute sets'),
                'required' => true,
                'values' => $this->attributeSetOptions->toOptionArray(),
                'disabled' => $isElementDisabled,
            ]
        );

        $field = $fieldset->addField(
            'config_filters_map_replace_empty_columns',
            'text',
            [
                'name' => 'config[filters_map_replace_empty_columns]',
                'label' => __('Replace empty values'),
                'title' => __('Replace empty values'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'note' => __('Columns must exist in <a href="#" data-tab-id="#feed_tabs_columns">Columns Map</a>. <strong>Save you config before looking for a new columns here.</strong> Grid has similar functions as <a href="#" data-tab-id="#feed_tabs_columns">Columns Map</a>'),
            ]
        );

        $renderer = $this->getLayout()->createBlock(
            'MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Columns\ColumnsMap'
        )->setMapExistingColumns(true)
            ->setControlName('replaceEmptyControl');
        $field->setRenderer($renderer);

        $field = $fieldset->addField(
            'config_filters_find_and_replace',
            'text',
            [
                'name' => 'config[filters_find_and_replace]',
                'label' => __('Find And Replace'),
                'title' => __('Find And Replace'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'note' => __('Save your colums before adding rules here. The find & replace will apply at column output. The more rules you apply the slower your feed will be, not recommended for large catalogs.'),
            ]
        );

        $renderer = $this->getLayout()->createBlock(
            'MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Filters\FindReplace'
        );
        $field->setRenderer($renderer);

        $field = $fieldset->addField(
            'config_filters_output_limit',
            'text',
            [
                'name' => 'config[filters_output_limit]',
                'label' => __('Limit column output'),
                'title' => __('Limit column output'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'note' => __('Set character limit by column output string, Over the limit chars get truncated. If you set more than one limit per column, only first rule will have effect.'),
            ]
        );

        $renderer = $this->getLayout()->createBlock(
            'MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Filters\OutputLimit'
        );
        $field->setRenderer($renderer);

        $fieldset->addField(
            'config_filters_skip_column_empty',
            'multiselect',
            [
                'name' => 'config[filters_skip_column_empty]',
                'label' => __('Skip Products with empty'),
                'title' => __('Skip Products with empty'),
                'required' => true,
                'values' => $this->sourceProductColumns->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Avoid having empty values for your items in the feed. Columns must exist in <a href="#" data-tab-id="#feed_tabs_columns">Columns Map</a>, save your config before looking for columns here.')
            ]
        );

        $this->_eventManager->dispatch(
            sprintf('mageos_shopping_feed_feed_edit_tab_filters_prepare_form_%s', $model->getType()), [
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
        return __('Product Filters');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Product Filters');
    }

    /**
     * Prepare tab notice
     *
     * @return string
     */
    public function getTabNotice()
    {
        return __('Filters here apply in the order as listed on the screen. Replace empty values rules use the order column for processing, and they can be set to create nested fill rules.');
    }
}
