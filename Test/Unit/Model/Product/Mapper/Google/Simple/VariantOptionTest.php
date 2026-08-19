<?php
/**
 * Copyright © 2016 Rocket Web Inc. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit\Model\Product\Mapper\Google\Simple;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use MageOS\ShoppingFeed\Test\Unit\Model\ModelFramework;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class VariantOptionTest extends ModelFramework
{
    public function testMapUsesConfigurableAttributes(): void
    {
        $adapter = $this->getModelMock(
            'MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple',
            ['hasParentAdapter', 'getParentAdapter', 'getProduct', 'getAttributeValue', 'getFilter']
        );
        $parentAdapter = $this->getModelMock(
            'MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple',
            ['getProduct']
        );
        $parentProduct = clone $this->productMock;
        $productType = $this->getModelMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            ['getConfigurableAttributes']
        );
        $configurableAttribute = $this->getModelMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute',
            ['getProductAttribute']
        );
        $attribute = $this->getModelMock(
            'Magento\Catalog\Model\ResourceModel\Eav\Attribute',
            ['getAttributeCode']
        );

        $this->expectReturn($adapter, 'hasParentAdapter', true);
        $this->expectReturn($adapter, 'getParentAdapter', $parentAdapter);
        $this->expectReturn($adapter, 'getProduct', $this->productMock);
        $this->expectReturn($adapter, 'getFilter', $this->filterMock);
        $this->expectReturn($parentAdapter, 'getProduct', $parentProduct);
        $this->expectReturn($parentProduct, 'getTypeInstance', $productType);
        $this->expectReturn($productType, 'getConfigurableAttributes', [$configurableAttribute]);
        $this->expectReturn($configurableAttribute, 'getProductAttribute', $attribute);
        $this->expectReturn($attribute, 'getAttributeCode', 'color');
        $this->expectReturn($this->productMock, 'hasData', true);
        $this->expectReturn($adapter, 'getAttributeValue', 'Red, Blue');

        $model = (new ObjectManagerHelper($this))->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Google\Simple\VariantOption'
        );
        $model->addAdapter($adapter);

        $this->assertSame(
            'color:"Red, Blue"',
            $model->map(['column' => 'variant_option'])
        );
    }
}
