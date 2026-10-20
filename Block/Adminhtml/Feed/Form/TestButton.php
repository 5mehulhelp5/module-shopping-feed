<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Block\Adminhtml\Feed\Form;

class TestButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        private \Magento\Backend\Model\UrlInterface $url,
        private \Magento\Framework\Registry $registry
    ) {
    }

    /**
     * Get the Test Feed button that opens the test popup for a saved feed
     *
     * @return array<string, mixed>
     */
    public function getButtonData(): array
    {
        $feed = $this->registry->registry('feed');
        if (!$feed || !$feed->getId()) {
            return [];
        }
        return ['label' => __('Test Feed'), 'sort_order' => 20,
            'on_click' => 'window.open(' . json_encode($this->url->getUrl('*/*/test', ['id' => $feed->getId()]))
                . ', "feed_test", "width=1200,height=800,scrollbars=yes,resizable=yes")'];
    }
}
