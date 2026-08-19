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
 * Feed edit form Bundle Products tab block
 */
namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab;

/**
 * Feed edit form Bundle Products tab
 */
class Bundle extends \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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

        $fieldset = $form->addFieldset('feed_bundle', ['legend' => __('Bundle Products')]);

        $fieldset->addField(
            'config_bundle_associated_products_mode',
            'select',
            [
                'name' => 'config[bundle_associated_products_mode]',
                'label' => __('How to add option products'),
                'title' => __('How to add option products'),
                'required' => true,
                'values' => $this->sourceAssociatedMode->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Bundle products are usually added as one item in the feed. Bundle sub-items could also be added.'),
            ]
        );

        $fieldset->addField(
            'config_bundle_combined_weight',
            'select',
            [
                'name' => 'config[bundle_combined_weight]',
                'label' => __('Combined weight'),
                'title' => __('Combined weight'),
                'required' => true,
                'values' => $this->sourceYesno->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Bundle items can be defined as Dynamic or Fixed weight. This feature overwrites the bundle defintition and goes for Dynamic weight by computing weight as sum of all sub-items.'),
            ]
        );

        $this->_eventManager->dispatch(
            sprintf('mageos_shopping_feed_feed_edit_tab_bundle_prepare_form_%s', $model->getType()), [
            'form' => $form,
            'feed' => $model,
            'is_element_disabled' => $isElementDisabled,
            ]
        );

        $form->setValues($this->prepareValues($model));
        $this->setForm($form);

        return $this;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Bundle Products');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Bundle Products');
    }

    /**
     * Prepare tab notice
     *
     * @return string
     */
    public function getTabNotice()
    {
        return __('This section applies to all bundle produts in your catalog. Bundle type should be enabled under <a href="#" data-tab-id="#feed_tabs_filters">Filters</a> section.');
    }
}
