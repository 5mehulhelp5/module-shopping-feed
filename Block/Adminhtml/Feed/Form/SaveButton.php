<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Form;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton implements ButtonProviderInterface
{
    public function __construct(private \Magento\Framework\AuthorizationInterface $authorization)
    {
    }

    public function getButtonData(): array
    {
        if (!$this->authorization->isAllowed('MageOS_ShoppingFeed::save')) {
            return [];
        }
        return [
            'label' => __('Save'), 'class' => 'save primary', 'sort_order' => 90,
            'data_attribute' => $this->action(true),
            'class_name' => \Magento\Ui\Component\Control\Container::SPLIT_BUTTON,
            'options' => [[
                'label' => __('Save and Continue Edit'), 'id_hard' => 'save_and_continue',
                'data_attribute' => $this->action(false)
            ]]
        ];
    }

    private function action(bool $redirect): array
    {
        return ['mage-init' => ['buttonAdapter' => ['actions' => [[
            'targetName' => 'mageos_shopping_feed_form.mageos_shopping_feed_form',
            'actionName' => 'save', 'params' => [$redirect]
        ]]]]];
    }
}
