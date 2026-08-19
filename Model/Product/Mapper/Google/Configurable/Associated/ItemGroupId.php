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
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Google\Configurable\Associated;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

class ItemGroupId extends MapperAbstract
{
    public function map(array $params = [])
    {
        $attributeName = isset($params['param']) && !empty($params['param']) ? $params['param'] : 'sku';
        $code = $this->getAdapter()->getParentAdapter()->getProduct()->getData($attributeName);

        $options = $this->getAdapter()->getOptionProcessor()->getOptions([$params['column'] => [$attributeName]]);
        $optionsList = [];
        /** @var \Magento\Catalog\Model\Product\Option $option */
        foreach ($options as $option) {
            $optionsList[] = $option->getTitle();
        }

        $variableColumns = $this->getVariableColumns($options);

        // Suffix the Parent SKU with non-variable option values
        $suffixes = [];
        $diff = array_diff(array_values($options), array_keys($variableColumns));
        foreach ($diff as $attributeCode) {
            $attribute = $this->getAdapter()->getMapAttribute($attributeCode);
            $suffixes[] = $this->getAdapter()->getAttributeValue($this->getAdapter()->getProduct(), $attribute);
        }

        $suffixedCode = $this->mergePieces($code, $suffixes);

        return count($suffixes) ? $suffixedCode : $code;
    }

    /**
     * @param $options
     * @return array
     */
    protected function getVariableColumns($options)
    {
        $columns = [];
        foreach ($this->getAdapter()->getFeed()->getColumnsMap() as $map) {
            $column = $map['column'];
            if (in_array($column, ['color', 'size', 'material', 'pattern'])) {
                if (in_array($map['attribute'], $options)) {
                    $columns[$map['attribute']] = $column;
                } elseif (isset($map['param'])) {
                    if (is_array($map['param'])) {
                        foreach ($map['param'] as $value) {
                            $value = strtolower($value);
                            if (in_array($value, $options)) {
                                $columns[$value] = $column;
                            }
                        }
                    } elseif (in_array($map['param'], $options)) {
                        $columns[$map['param']] = $column;
                    }
                }
            }
        }

        return $columns;
    }

    /**
     * @param $code
     * @param $suffixes
     * @return string
     */
    protected function mergePieces($code, $suffixes)
    {
        $result = $code . '-' . implode('-', $suffixes);
        if (count($suffixes) && strlen($result) >= 50) {
            if (strlen($code) >= 9) {
                // We hash the whole string since sha1() returns 40 chars and we hit 50 total
                $result = sha1($result);
            } else {
                // For at least a bit easier readability we hash only suffixes
                $onlySuffixes = implode('-', $suffixes);
                $result = $code . '-' . sha1($onlySuffixes);
            }
        }
        return $result;
    }
}
