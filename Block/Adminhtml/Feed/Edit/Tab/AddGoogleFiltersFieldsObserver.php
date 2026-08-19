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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddGoogleFiltersFieldsObserver implements ObserverInterface
{
    /**
     * Parent layout of the block
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context
    ) {
        $this->layout = $context->getLayout();
    }

    /**
     * Adds Google Shopping fields to Filters tab feed edit page
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $feed = $observer->getEvent()->getFeed();
        $form = $observer->getEvent()->getForm();
        $isElementDisabled = $observer->getEvent()->getIsElementDisabled();

        if ($form->getElement('feed_filters') && $feed->getType() == 'google_shopping') {
            $fieldset = $form->getElement('feed_filters');
            $fieldset->addField(
                'config_filters_skip_price_above',
                'text',
                [
                    'name' => 'config[filters_skip_price_above]',
                    'label' => __('Skip Products with Price above'),
                    'title' => __('Skip Products with Price above'),
                    'required' => false,
                    'disabled' => $isElementDisabled,
                    'note' => __('Products with price above value specified would be skipped. Filter does not apply when empty'),
                ]
            );

            $fieldset->addField(
                'config_filters_skip_price_below',
                'text',
                [
                    'name' => 'config[filters_skip_price_below]',
                    'label' => __('Skip Products with Price below'),
                    'title' => __('Skip Products with Price below'),
                    'required' => false,
                    'disabled' => $isElementDisabled,
                    'note' => __('Products with price below value specified would be skipped. Filter does not apply when empty'),
                ]
            );

            $field = $fieldset->addField(
                'config_filters_adwords_price_buckets',
                'text',
                [
                    'name' => 'config[filters_adwords_price_buckets]',
                    'label' => __('Adwords Price Buckets'),
                    'title' => __('Adwords Price Buckets'),
                    'required' => false,
                    'disabled' => $isElementDisabled,
                    'note' => __('This grid is used to build a value in the column assigned to the "Adwords Price Buckets" directive, under <a href="#" data-tab-id="#feed_tabs_columns">Columns Map</a>. Values with empty order are matched last.'),
                ]
            );

            $renderer = $this->layout->createBlock(
                'MageOS\ShoppingFeed\Block\Adminhtml\Feed\Edit\Tab\Filters\PriceBuckets'
            );
            $field->setRenderer($renderer);
        }
    }
}
