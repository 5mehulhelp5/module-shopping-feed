<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Product\Formatter\OpenAi;

use MageOS\ShoppingFeed\Model\Product\Formatter\Price\Currency;

/** Preserve zero regular prices so the row validator can check the mobile subscription exception. */
class Price extends Currency
{
    /** Format zero without changing the shared sale-price formatter. */
    public function run($var)
    {
        if (!is_numeric($var) || (float)$var !== 0.0) {
            return parent::run($var);
        }
        $value = sprintf('0.00 %s', $this->getAdapter()->getData('store_currency_code'));
        if ($this->getColumn() !== null) {
            $this->getAdapter()->getFilter()->findAndReplace($value, $this->getColumn());
        }
        return $value;
    }
}
