<?php
/**
 * Copyright © 2016 Rocket Web Inc. (https://rocketweb.com/)
 * Licensed under the Open Software License version 3.0. See LICENSE.txt.
 */

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Google\Simple;

use MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

class ItemGroupTitle extends MapperAbstract
{
    /**
     * @inheritdoc
     */
    public function map(array $params = []): string
    {
        $adapter = $this->getAdapter();
        if (!$adapter->hasParentAdapter()) {
            return '';
        }

        $title = (string)$adapter->getParentAdapter()->getProduct()->getData('name');

        return $adapter->getFilter()->cleanField($title, $params);
    }
}
