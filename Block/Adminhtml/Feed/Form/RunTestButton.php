<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Form;

class RunTestButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    public function getButtonData(): array
    {
        return ['label' => __('Test Now'), 'class' => 'save primary', 'sort_order' => 90, 'on_click' => '',
            'data_attribute' => ['mage-init' => ['buttonAdapter' => ['actions' => [[
                'targetName' => 'mageos_shopping_feed_test_form.mageos_shopping_feed_test_form',
                'actionName' => 'save', 'params' => [true]
            ]]]]]];
    }
}
