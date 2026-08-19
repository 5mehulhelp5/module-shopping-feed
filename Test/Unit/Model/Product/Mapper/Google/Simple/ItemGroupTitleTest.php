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
class ItemGroupTitleTest extends ModelFramework
{
    public function testMapUsesParentProductTitle(): void
    {
        $adapter = $this->getModelMock(
            'MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple',
            ['hasParentAdapter', 'getParentAdapter', 'getFilter']
        );
        $parentAdapter = $this->getModelMock(
            'MageOS\ShoppingFeed\Model\Product\Adapter\Type\Simple',
            ['getProduct']
        );
        $parentProduct = clone $this->productMock;

        $this->expectReturn($adapter, 'hasParentAdapter', true);
        $this->expectReturn($adapter, 'getParentAdapter', $parentAdapter);
        $this->expectReturn($adapter, 'getFilter', $this->filterMock);
        $this->expectReturn($parentAdapter, 'getProduct', $parentProduct);
        $this->expectReturn($parentProduct, 'getData', 'Vila Lounge Chair');

        $model = (new ObjectManagerHelper($this))->getObject(
            'MageOS\ShoppingFeed\Model\Product\Mapper\Google\Simple\ItemGroupTitle'
        );
        $model->addAdapter($adapter);

        $this->assertSame(
            'Vila Lounge Chair',
            $model->map(['column' => 'item_group_title'])
        );
    }
}
