<?php
/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   MageOS_ShoppingFeed
 * @copyright Copyright (c) 2023 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */

namespace MageOS\ShoppingFeed\Model\Product\Mapper\LocalInventory\Simple;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperStock;


class InventorySource extends MapperStock
{
    public function map(array $params = [])
    {
        $sourceCode = 'default';
        if (!$this->usesDefaultStock()) {
            $sourceCode = 'custom';
        } else {
            $sourceItem = $this->getAdapter()->getData('inventory_source_item');
            if ($sourceItem) {
                $sourceCode = $sourceItem->getSourceCode();
            }
        }

        $storeCode = $this->getStoreCodeMap()[$sourceCode] ?? $sourceCode;
        return $this->getAdapter()->getFilter()->cleanField($storeCode, $params);
    }

    /**
     * @return array<string, string>
     */
    private function getStoreCodeMap()
    {
        $value = (string) $this->getAdapter()->getFeed()->getConfig('categories_inventory_source_map', '');
        $map = [];

        foreach (preg_split('/\R/', $value) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '=') === false) {
                continue;
            }

            [$sourceCode, $storeCode] = array_map('trim', explode('=', $line, 2));
            if ($sourceCode !== '' && $storeCode !== '') {
                $map[$sourceCode] = $storeCode;
            }
        }

        return $map;
    }
}
