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
 * @copyright Copyright (c) 2023 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Categories;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class LocalInventoryFilterFields implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $feed = $observer->getEvent()->getFeed();
        $form = $observer->getEvent()->getForm();

        if ($form->getElement('feed_categories') && $feed->getType() == 'google_local_inventory') {
            $fieldset = $form->getElement('feed_categories');
            $fieldset->removeField('config_categories_locale');
            $fieldset->removeField('config_categories_sort_mode');

            $fieldset->addField(
                'config_categories_inventory_source_map',
                'textarea',
                [
                    'name' => 'config[categories_inventory_source_map]',
                    'label' => __('Inventory Source to Google Store Code'),
                    'title' => __('Inventory Source to Google Store Code'),
                    'required' => false,
                    'disabled' => (bool) $observer->getEvent()->getData('is_element_disabled'),
                    'note' => __(
                        'Enter one mapping per line as source_code=store_code. '
                        . 'Unmapped sources keep their Magento source code. Example: warehouse_indy=INDIANAPOLIS-01'
                    ),
                ]
            );

            $field = $form->getElement('config_categories_provider_taxonomy_by_category');
            $renderer = $field->getRenderer();
            $renderer->setTemplate('MageOS_ShoppingFeed::feed/edit/tab/categories/category-local-inventory.phtml');
        }
    }
}
