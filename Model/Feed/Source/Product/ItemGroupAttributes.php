<?php

namespace MageOS\ShoppingFeed\Model\Feed\Source\Product;

class ItemGroupAttributes
{
    public function toOptionArray()
    {
        return [
            ['value' => 'entity_id', 'label' => __('Id')],
            ['value' => 'sku', 'label' => __('SKU')],
        ];
    }
}
