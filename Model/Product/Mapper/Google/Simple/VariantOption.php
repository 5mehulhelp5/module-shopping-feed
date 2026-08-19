<?php
/**
 * Copyright © 2016 Rocket Web Inc. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Google\Simple;

use MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

class VariantOption extends MapperAbstract
{
    public function map(array $params = []): string
    {
        $adapter = $this->getAdapter();
        if (!$adapter->hasParentAdapter()) {
            return '';
        }

        $parentProduct = $adapter->getParentAdapter()->getProduct();
        $productType = $parentProduct->getTypeInstance();
        if (!method_exists($productType, 'getConfigurableAttributes')) {
            return '';
        }

        $product = $adapter->getProduct();
        $options = [];
        foreach ($productType->getConfigurableAttributes($parentProduct) as $configurableAttribute) {
            $attribute = $configurableAttribute->getProductAttribute();
            $attributeCode = $attribute->getAttributeCode();
            if (!$product->hasData($attributeCode)) {
                continue;
            }

            $value = $adapter->getAttributeValue($product, $attribute);
            $value = $adapter->getFilter()->cleanField($value, $params);
            if ($value !== '') {
                $options[] = $attributeCode . ':' . $this->quoteValue($value);
            }
        }

        return implode(',', $options);
    }

    private function quoteValue(string $value): string
    {
        if (strpbrk($value, ':,"') === false) {
            return $value;
        }

        return '"' . str_replace('"', '""', $value) . '"';
    }
}
