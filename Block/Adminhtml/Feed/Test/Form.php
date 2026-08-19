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

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Test;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Source\Test\Type
     */
    protected $type;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \MageOS\ShoppingFeed\Model\Generator\Source\Test\Type $type,
        array $data = []
    ) {

        parent::__construct($context, $registry, $formFactory, $data);
        $this->type = $type->setForm($this);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \MageOS\ShoppingFeed\Model\Feed */
        $model = $this->_coreRegistry->registry('feed');

        /**
 * @var \Magento\Framework\Data\Form $form
*/
        $form = $this->_formFactory->create(
            [
            'data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']
            ]
        );

        $form->setHtmlIdPrefix('feed_test_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Which product ?')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'sku',
            'text',
            [
                'name' => 'sku',
                'note' => __(
                    'SKU here must be is visible in catalog and enabled. ' .
                    'If you want to test a sub-item, you must fill in the parent SKU here.'
                ),
                'before_element_html' => $this->type->toHtml(),
                'required' => true,
                'disabled' => false,
                'style' => 'width: 300px;'
            ]
        );

        $formValues = $model->getData();

        // Add results fieldset if sku is submitted
        $product = $this->_coreRegistry->registry('current_test_product');
        if ($product && $product->getId()) {
            $resultFieldset = $form->addFieldset('result_fieldset', ['legend' => __('Test Results')]);
            $field = $resultFieldset->addField(
                'result',
                'text',
                [
                    'name' => 'result',
                    'label' => __('Results'),
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'MageOS\ShoppingFeed\Block\Adminhtml\Feed\Test\Result'
            );
            $field->setRenderer($renderer);
            $formValues['sku'] = $product->getSku();
        }

        $form->setUseContainer(true);
        $form->setValues($formValues);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
