<?php

namespace MageOS\ShoppingFeed\Model\Feed\Source\Product;

class ItemGroupAttributes
{
    /**
     * Product identifiers that can group variant items.
     *
     * @return array<int,array<string,mixed>>
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'entity_id', 'label' => __('Id')],
            ['value' => 'sku', 'label' => __('SKU')],
        ];
    }
}
