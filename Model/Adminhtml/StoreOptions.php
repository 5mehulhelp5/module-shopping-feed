<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Model\Adminhtml;

class StoreOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    public function __construct(private \Magento\Store\Model\StoreManagerInterface $stores)
    {
    }

    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->stores->getStores() as $store) {
            $options[] = ['value' => $store->getId(), 'label' => $store->getName() . ' (' . $store->getCode() . ')'];
        }
        return $options;
    }
}
