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

namespace MageOS\ShoppingFeed\Model\Product\Mapper\Generic\Simple;

use \MageOS\ShoppingFeed\Model\Product\Mapper\MapperAbstract;

class Concatenate extends MapperAbstract
{
    public function getDirectiveNames()
    {
        return ['directive_concatenate'];
    }


    public function map(array $params = [])
    {
        $expr = $params['param'];
        preg_match_all('/\{\{(.*?)\}\}/is', $expr, $attributes);

        if (!isset($attributes[1]) || empty($attributes[1])) {
            $this->logger->warning('Invalid expression in Concatenate directive. Could not find product attributes', $params);
            return $expr;
        }

        // Get value for each identified attribute
        $values = [];
        foreach ($attributes[1] as $k => $attributeColumn) {
            $newParams = $this->buildMapParameters($attributeColumn);
            try {
                $values = $this->getAttributeValue($k, $attributeColumn, $values, $newParams);
            } catch (\Exception $e) {
                $values[$k] = "{{". $attributeColumn. "}}";
            }
        }

        $implodedValues = implode('', $values);
        if (empty($implodedValues)) {
            $expr = '';
        }

        foreach ($values as $k => $val) {
            $expr = str_replace($attributes[0][$k], $val, $expr);
        }

        return $this->getAdapter()->getFilter()->cleanField($expr, $params);
    }

    protected function buildMapParameters($attributeColumn)
    {
        $column = $this->getColumn($attributeColumn);
        $params = ['column' => $column, 'attribute' => $column];

        // if the placeholder is a column name, use it's column map
        $columnsMap = $this->getAdapter()->getFeed()->getColumnsMap();
        foreach ($columnsMap as $arr) {
            if ($params['column'] == $arr['column']) {
                $params['attribute'] = $arr['attribute'];
                $params['param'] = isset($arr['param']) ? $arr['param'] : '';

                if (in_array($params['attribute'], $this->getDirectiveNames())) {
                    $params['attribute'] = $attributeColumn;
                }
                break;
            }
        }

        return $params;
    }

    protected function getColumn($attributeColumn)
    {
        return $attributeColumn;
    }

    protected function getAttributeValue($key, $attributeColumn, $values = [], $params = [])
    {
        $values[$key] = $this->getAdapter()->getMapValue($params);

        if ($values[$key] === '') {
            $values[$key] = $this->getAdapter()->mapEmptyValues($params);
        }

        return $values;
    }
}
