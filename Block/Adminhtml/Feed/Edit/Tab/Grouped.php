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
 * Feed edit form Grouped Products tab block
 */
namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab;

/**
 * Feed edit form Grouped Products tab
 */
class Grouped extends \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\Grouped\PriceType
     */
    protected $sourcePriceType;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode
     */
    protected $sourceAssociatedMode;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $sourceYesno;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns
     */
    protected $sourceProductColumns;

    /**
     * @param \Magento\Backend\Block\Template\Context                              $context
     * @param \Magento\Framework\Registry                                          $registry
     * @param \Magento\Framework\Data\FormFactory                                  $formFactory
     * @param \MageOS\ShoppingFeed\Model\Feed\Converter                        $feedConverter
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode    $sourceAssociatedMode
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\Grouped\PriceType $sourcePriceType
     * @param \Magento\Config\Model\Config\Source\Yesno                            $sourceYesno
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns           $sourceProductColumns
     * @param array                                                                $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \MageOS\ShoppingFeed\Model\Feed\Converter $feedConverter,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode $sourceAssociatedMode,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\Grouped\PriceType $sourcePriceType,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns $sourceProductColumns,
        array $data = []
    ) {
        $this->sourceAssociatedMode = $sourceAssociatedMode;
        $this->sourceYesno = $sourceYesno;
        $this->sourcePriceType = $sourcePriceType;
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

        $fieldset = $form->addFieldset('feed_grouped', ['legend' => __('Grouped Products')]);

        $fieldset->addField(
            'config_grouped_associated_products_mode',
            'select',
            [
                'name' => 'config[grouped_associated_products_mode]',
                'label' => __('How to add associated products'),
                'title' => __('How to add associated products'),
                'required' => true,
                'values' => $this->sourceAssociatedMode->toOptionArray(),
                'disabled' => $isElementDisabled,
            ]
        );

        $fieldset->addField(
            'config_grouped_add_out_of_stock',
            'select',
            [
                'name' => 'config[grouped_add_out_of_stock]',
                'label' => __('Allow Out of Stock'),
                'title' => __('Allow Out of Stock'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('For associated products of configurable products.'),
            ]
        );

        $fieldset->addField(
            'config_grouped_associated_products_link_add_unique',
            'select',
            [
                'name' => 'config[grouped_associated_products_link_add_unique]',
                'label' => __('Unique urls for associated products not visible'),
                'title' => __('Unique urls for associated products not visible'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('The new unique url will be formed from grouped product url and the ids of the associated product. I.e http://example.com/grouped.html?prod_id=123'),
            ]
        );

        $fieldset->addField(
            'config_grouped_price_display_mode',
            'select',
            [
                'name' => 'config[grouped_price_display_mode]',
                'label' => __('Price Type'),
                'title' => __('Price Type'),
                'required' => true,
                'values' => $this->sourcePriceType->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('"Minimal price" is the lowest associated product price.<br />"Sum of associated products prices" is the default quantity of each associated product multiplied with the price of the associated product and than summed together. If no default quantity is defined, it will output minimal price.'),
            ]
        );

        $field = $fieldset->addField(
            'config_grouped_map_inherit',
            'text',
            [
                'name' => 'config[grouped_map_inherit]',
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
            sprintf('mageos_shopping_feed_feed_edit_tab_grouped_prepare_form_%s', $model->getType()), [
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
        return __('Grouped Products');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Grouped Products');
    }

    /**
     * Prepare tab notice
     *
     * @return string
     */
    public function getTabNotice()
    {
        return __('This section applyes to all grouped and their associated produts in your catalog. Grouped type should be enabled under <a href="#" data-tab-id="#feed_tabs_filters">Filters</a> section.');
    }
}
