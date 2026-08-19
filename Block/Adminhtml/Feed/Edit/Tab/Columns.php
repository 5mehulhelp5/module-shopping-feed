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
 * Feed edit form Columns Map tab block
 */
namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab;

/**
 * Feed edit form Columns Map tab
 */
class Columns extends \MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
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

        $fieldset = $form->addFieldset('feed_columns', ['legend' => __('Columns Map')]);

        $field = $fieldset->addField(
            'config_columns_product_columns',
            'text',
            [
                'name' => 'config[columns_product_columns]',
                'label' => __('Columns Map'),
                'title' => __('Columns Map'),
                'required' => false,
                'disabled' => $isElementDisabled,
            ]
        );

        $renderer = $this->getLayout()->createBlock(
            'MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Columns\ColumnsMap'
        )->setMapExistingColumns(false)->setControlName('columnsMapControl');
        $field->setRenderer($renderer);

        $this->_eventManager->dispatch(
            sprintf('mageos_shopping_feed_feed_edit_tab_columns_prepare_form_%s', $model->getType()), [
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
        return __('Columns Map');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Columns Map');
    }

    /**
     * Prepare tab notice
     *
     * @return string
     */
    public function getTabNotice()
    {
        return __('Feed output depends on your store configuration, types of products, available data stored in your products and others.');
    }
}
