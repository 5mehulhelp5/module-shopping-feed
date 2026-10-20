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
    /**
     * Directive codes this mapper handles.
     *
     * @return string[]
     */
    public function getDirectiveNames()
    {
        return ['directive_concatenate'];
    }

    /**
     * Expand the {{attribute}} placeholders of the column param with the product's mapped values.
     *
     * @param array $params
     * @return mixed
     */
    public function map(array $params = [])
    {
        $expr = $params['param'];
        preg_match_all('/\{\{(.*?)\}\}/is', $expr, $attributes);

        if (empty($attributes[1])) {
            $this->logger->warning(
                'Invalid expression in Concatenate directive. Could not find product attributes',
                $params
            );
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

    /**
     * Column map entry for a placeholder: the matching feed column, else the attribute itself.
     *
     * @param string $attributeColumn Placeholder name
     * @return array<string,mixed>
     */
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

    /**
     * Column name a placeholder refers to.
     *
     * @param string $attributeColumn Placeholder name
     * @return string
     */
    protected function getColumn($attributeColumn)
    {
        return $attributeColumn;
    }

    /**
     * Add the mapped value of one placeholder to the collected values.
     *
     * @param int|string $key Placeholder index
     * @param string $attributeColumn Placeholder name
     * @param array<mixed> $values Values collected so far
     * @param array<string,mixed> $params Column map entry for the placeholder
     * @return array<mixed>
     */
    protected function getAttributeValue($key, $attributeColumn, $values = [], $params = [])
    {
        $values[$key] = $this->getAdapter()->getMapValue($params);

        if ($values[$key] === '') {
            $values[$key] = $this->getAdapter()->mapEmptyValues($params);
        }

        return $values;
    }
}
