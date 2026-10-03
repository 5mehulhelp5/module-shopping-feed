<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Form;

class BackButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    public function __construct(private \Magento\Backend\Model\UrlInterface $url)
    {
    }

    public function getButtonData(): array
    {
        return ['label' => __('Back'), 'class' => 'back', 'sort_order' => 10,
            'on_click' => 'location.href = ' . json_encode($this->url->getUrl('*/*/'))];
    }
}
