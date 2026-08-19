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
class Categories extends \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Locale
     */
    protected $sourceLocale;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $sourceYesno;

    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Category\PriorityMode
     */
    protected $sourcePriorityMode;

    /**
     * @param \Magento\Backend\Block\Template\Context                          $context
     * @param \Magento\Framework\Registry                                      $registry
     * @param \Magento\Framework\Data\FormFactory                              $formFactory
     * @param \MageOS\ShoppingFeed\Model\Feed\Converter                    $feedConverter
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Locale                $sourceLocale
     * @param \Magento\Config\Model\Config\Source\Yesno                        $sourceYesno
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Category\PriorityMode $sourcePriorityMode
     * @param array                                                            $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \MageOS\ShoppingFeed\Model\Feed\Converter $feedConverter,
        \MageOS\ShoppingFeed\Model\Feed\Source\Locale $sourceLocale,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        \MageOS\ShoppingFeed\Model\Feed\Source\Category\PriorityMode $sourcePriorityMode,
        array $data = []
    ) {
        $this->sourceLocale = $sourceLocale;
        $this->sourceYesno = $sourceYesno;
        $this->sourcePriorityMode = $sourcePriorityMode;
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

        $fieldset = $form->addFieldset('feed_categories', ['legend' => __('Categories Map')]);

        $fieldset->addField(
            'config_categories_locale',
            'select',
            [
                'name' => 'config[categories_locale]',
                'label' => __('Feed Localization'),
                'title' => __('Feed Localization'),
                'required' => true,
                'values' => $this->sourceLocale->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Changing the language of your feed affects how Apparels are matched using Google taxonomies. Your products should also be in the same language. Refer to \'Google Category of the Item\' attribute. This setting does not affect price formatting, assure proper store language for that.'),
            ]
        );

        $fieldset->addField(
            'config_categories_include_all_products',
            'select',
            [
                'name' => 'config[categories_include_all_products]',
                'label' => __('Include products w/o category'),
                'title' => __('Include products w/o category'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('If enabled, products that do not belog to a category will be added to the feed along with ones from disabled categories. Note that the taxonomy will be missing in this case, so you can capture that in a Replace Empty rule under Filters.'),
            ]
        );

        $fieldset->addField(
            'config_categories_sort_mode',
            'select',
            [
                'name' => 'config[categories_sort_mode]',
                'label' => __('Categories priority mode'),
                'title' => __('Categories priority mode'),
                'required' => true,
                'values' => $this->sourcePriorityMode->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('If set to use priority of categories of the same level, deeper level categories are mateched first, than apply the priority of categories at the same level to detemine which one will be matched for a product with multiple categories.'),
            ]
        );

        $field = $fieldset->addField(
            'config_categories_provider_taxonomy_by_category',
            'text',
            [
                'name' => 'config[categories_provider_taxonomy_by_category]',
                'label' => __('Taxonomy by Magento Category'),
                'title' => __('Taxonomy by Magento Category'),
                'required' => false,
                'disabled' => $isElementDisabled,
            ]
        );

        $renderer = $this->getLayout()->createBlock(
            'MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Categories\CategoryTaxonomy'
        );
        $field->setRenderer($renderer);

        $this->_eventManager->dispatch(
            sprintf('mageos_shopping_feed_feed_edit_tab_categories_prepare_form_%s', $model->getType()), [
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
        return __('Categories Map');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Categories Map');
    }

    /**
     * Prepare tab notice
     *
     * @return string
     */
    public function getTabNotice()
    {
        return __('Category definitions here, apply to the directive called <strong>Taxonomy By Magento Category</strong> and <strong>Type By Magento Category</strong>.');
    }
}
