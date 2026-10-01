<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Adminhtml;

class StockAttributes implements \Magento\Framework\Data\OptionSourceInterface
{
    public function __construct(private \Magento\Framework\App\ResourceConnection $resource)
    {
    }

    public function toOptionArray(): array
    {
        $columns = $this->resource->getConnection()->describeTable($this->resource->getTableName('cataloginventory_stock_item'));
        $options = [];
        foreach (array_keys($columns) as $code) {
            if (substr($code, -3) !== '_id') {
                $options[] = ['value' => $code, 'label' => __(ucwords(str_replace('_', ' ', $code)))];
            }
        }
        return $options;
    }
}
