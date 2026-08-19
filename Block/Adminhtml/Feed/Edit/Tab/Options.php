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
 * Feed edit form Product Options tab block
 */
namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab;

/**
 * Feed edit form Product Options tab
 */
class Options extends \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Feed\Source\Product\OptionHandling
     */
    protected $sourceOptionHandling;

    /**
     * @param \Magento\Backend\Block\Template\Context                           $context
     * @param \Magento\Framework\Registry                                       $registry
     * @param \Magento\Framework\Data\FormFactory                               $formFactory
     * @param \MageOS\ShoppingFeed\Model\Feed\Converter                     $feedConverter
     * @param \MageOS\ShoppingFeed\Model\Feed\Source\Product\OptionHandling $sourceOptionHandling
     * @param array                                                             $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \MageOS\ShoppingFeed\Model\Feed\Converter $feedConverter,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\OptionHandling $sourceOptionHandling,
        array $data = []
    ) {
        $this->sourceOptionHandling = $sourceOptionHandling;
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

        $fieldset = $form->addFieldset('feed_options', ['legend' => __('Product Options')]);

        $fieldset->addField(
            'config_options_mode',
            'select',
            [
                'name' => 'config[options_mode]',
                'label' => __('How to add product options'),
                'title' => __('How to add product options'),
                'required' => true,
                'values' => $this->sourceOptionHandling->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => __('Detail product options into one single row or multiple rows, one for each option.'),
            ]
        );

        $fieldset->addType(
            'options_vary_categories',
            'MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Options\Category'
        );
        $fieldset->addField(
            'config_options_vary_categories',
            'options_vary_categories',
            [
                'name' => 'config[options_vary_categories]',
                'label' => __('Multiple rows only for products in these categories'),
                'title' => __('Multiple rows only for products in these categories'),
                'required' => false,
                'disabled' => $isElementDisabled,
            ]
        );

        $this->_eventManager->dispatch(
            sprintf('mageos_shopping_feed_feed_edit_tab_categories_prepare_form_%s', $model->getType()), [
            'form' => $form,
            'feed' => $model,
            'is_element_disabled' => $isElementDisabled,
            ]
        );
        $values = $this->prepareValues($model);
        if (!$values['config_options_vary_categories']) {
            $values['config_options_vary_categories'] = [];
        }

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
        return __('Product Options');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Product Options');
    }

    /**
     * Prepare tab notice
     *
     * @return string
     */
    public function getTabNotice()
    {
        return __('In order for the feed to varry products by their options, the <strong>Feed Columns</strong> needs to have the variant columns (i.e. size and color) mapped to the <strong>Product Options</strong> directive.<br />Use the <strong>Product Option</strong> directive parameter to specify which options should be pulled.');
    }
}
