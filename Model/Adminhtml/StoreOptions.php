<?php
declare(strict_types=1);
namespace MageOS\ShoppingFeed\Model\Adminhtml;

class StoreOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @param \Magento\Store\Model\StoreManagerInterface $stores
     */
    public function __construct(private \Magento\Store\Model\StoreManagerInterface $stores)
    {
    }

    /**
     * Store views as "Name (code)" options.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->stores->getStores() as $store) {
            $options[] = ['value' => $store->getId(), 'label' => $store->getName() . ' (' . $store->getCode() . ')'];
        }
        return $options;
    }
}
