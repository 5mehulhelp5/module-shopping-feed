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
 * Feed edit form Shipping tab block
 */
namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab;

/**
 * Feed edit form Shipping tab
 */
class Shipping extends \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Shipping\AvailableMethods $sourceShippingMethods
     */
    protected $sourceShippingMethods;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture $sourceCountry
     */
    protected $sourceCountry;

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
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Shipping\AvailableMethods $sourceShippingMethods
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture $sourceCountry
     * @param \Magento\Config\Model\Config\Source\Yesno                            $sourceYesno
     * @param array                                                                $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \MageOS\ShoppingFeed\Model\Feed\Converter $feedConverter,
        \MageOS\ShoppingFeed\Model\Feed\Source\Shipping\AvailableMethods $sourceShippingMethods,
        \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture $sourceCountry,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\Columns $sourceProductColumns,
        array $data = []
    ) {
        $this->sourceShippingMethods = $sourceShippingMethods;
        $this->sourceCountry = $sourceCountry;
        $this->sourceYesno = $sourceYesno;
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

        $fieldset = $form->addFieldset('feed_shipping', ['legend' => __('Shipping')]);

        $fieldset->addField(
            'config_shipping_methods',
            'multiselect',
            [
                'name' => 'config[shipping_methods]',
                'label' => __('Methods'),
                'title' => __('Methods'),
                'required' => true,
                'values' => $this->sourceShippingMethods->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Allowed shipping methods. Realtime carriers aren\'t allowed to avoid getting banned or to spam carriers\' servers. e.g. UPS, USPS, FedEx, DHL, Royal Mail, ..<br />Please add/configure any realtime carriers in your Google Merchant account.'),
            ]
        );

        $fieldset->addField(
            'config_shipping_country',
            'multiselect',
            [
                'name' => 'config[shipping_country]',
                'label' => __('Countries'),
                'title' => __('Countries'),
                'required' => true,
                'values' => $this->sourceCountry->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Shipping allowed countries. Select only a few countries the avoid a very long feed generation and to keep the feed size to a minimnum.'),
            ]
        );

        $fieldset->addField(
            'config_shipping_weight_column',
            'select',
            [
                'name' => 'config[shipping_weight_column]',
                'label' => __('Shipping Weight Column'),
                'title' => __('Shipping Weight Column'),
                'required' => false,
                'values' => $this->sourceProductColumns->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Set shipping weight column from which we calculate shipping costs. Columns must exist in <a href="#" data-tab-id="#feed_tabs_columns">Columns Map</a>, save your config before looking for columns here.'),
            ]
        );

        $fieldset->addField(
            'config_shipping_only_minimum',
            'select',
            [
                'name' => 'config[shipping_only_minimum]',
                'label' => __('Only Minimum Price'),
                'title' => __('Only Minimum Price'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('If there are more carriers/shipping methods than the shipping column will be filled with only the minimum price and the related carrier/shipping method.'),
            ]
        );

        $fieldset->addField(
            'config_shipping_only_free_shipping',
            'select',
            [
                'name' => 'config[shipping_only_free_shipping]',
                'label' => __('Only Free Shipping'),
                'title' => __('Only Free Shipping'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Add only free shipping when is available.'),
            ]
        );

        $fieldset->addField(
            'config_shipping_add_tax_to_price',
            'select',
            [
                'name' => 'config[shipping_add_tax_to_price]',
                'label' => __('Add Tax to Shipping Price'),
                'title' => __('Add Tax to Shipping Price'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('For US feeds column \'price\' should not include tax.'),
            ]
        );

        $this->_eventManager->dispatch(
            sprintf('mageos_shopping_feed_feed_edit_tab_shipping_prepare_form_%s', $model->getType()), [
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
        return __('Shipping');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Shipping');
    }

    /**
     * Prepare tab notice
     *
     * @return string
     */
    public function getTabNotice()
    {
        return __('This setting tunes how <strong>Shipping</strong> directive works. You must also add your shipping column under <a href="#" data-tab-id="#feed_tabs_columns">Columns Map</a> and map it to the <strong>Shipping</strong> directive.');
    }
}
