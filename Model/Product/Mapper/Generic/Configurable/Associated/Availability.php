<?php

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Configurable\Associated;

use \MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple\Availability as SimpleAvailability;

class Availability extends SimpleAvailability
{
    public function map(array $params = [])
    {
        $cell = self::IN_STOCK;
        if ($this->getAdapter()->getFeed()->getConfig('configurable_inherit_parent_out_of_stock')) {
            $parent = $this->getAdapter()->getParentAdapter();
            $cell = $this->usesDefaultStock()
                ? ($parent->getProduct()->isSalable() ? self::IN_STOCK : self::OUT_OF_STOCK)
                : $this->getStockStatus($parent);
        }

        if ($cell == self::IN_STOCK) {
            $cell = $this->getStockStatus($this->getAdapter());
        }

        return $this->getAdapter()->getFilter()->cleanField($cell, $params);
    }

    public function filter($cell)
    {
        if (!$this->getAdapter()->getFeed()->getConfig('configurable_add_out_of_stock')) {
            if ($cell == self::OUT_OF_STOCK) {
                return true;
            }
        }
        return false;
    }
}
