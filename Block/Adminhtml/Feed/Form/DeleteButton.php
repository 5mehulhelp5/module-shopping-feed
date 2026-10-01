<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Form;

class DeleteButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    public function __construct(
        private \Magento\Backend\Model\UrlInterface $url,
        private \Magento\Framework\Registry $registry,
        private \Magento\Framework\AuthorizationInterface $authorization
    ) {
    }

    public function getButtonData(): array
    {
        $feed = $this->registry->registry('feed');
        if (!$feed || !$feed->getId() || !$this->authorization->isAllowed('MageOS_ShoppingFeed::delete')) {
            return [];
        }
        return ['label' => __('Delete Feed'), 'class' => 'delete', 'sort_order' => 15,
            'on_click' => 'deleteConfirm(' . json_encode((string)__('Delete this feed?')) . ', '
                . json_encode($this->url->getUrl('*/*/delete', ['id' => $feed->getId()])) . ', {"data": {}})'];
    }
}
