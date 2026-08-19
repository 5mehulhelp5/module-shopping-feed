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


namespace MageOS\ShoppingFeed\Model\FeedTypes\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @var null|\Magento\Framework\Json\Decoder
     */
    private $jsonDecoder = null;

    /**
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * Convert dom node tree to array
     *
     * @param  \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function convert($source)
    {
        $output = [];
        $xpath = new \DOMXPath($source);
        $types = $xpath->evaluate('/config/feed');
        /**
 * @var $typeNode \DOMNode
*/
        foreach ($types as $typeNode) {
            $typeName = $this->getAttributeValue($typeNode, 'name');

            $data = [];
            $data['name'] = $typeName;
            $data['taxonomyProvider'] = $this->getAttributeValue($typeNode, 'taxonomyProvider');

            /**
 * @var $childNode \DOMNode
*/
            foreach ($typeNode->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                switch ($childNode->nodeName) {
                case 'label':
                    $data['label'] = $childNode->nodeValue;
                    break;
                case 'description':
                    $data['description'] = $childNode->nodeValue;
                    break;
                case 'directives':
                    $data['directives'] = [];
                    /**
 * @var $directive \DOMNode
*/
                    foreach ($childNode->childNodes as $directive) {
                        if ($directive->nodeType != XML_ELEMENT_NODE) {
                            continue;
                        }

                        $name = $this->getAttributeValue($directive, 'name');
                        $directiveData = [
                            'mappers' => [],
                            'allow_output_limit' =>
                                $this->getAttributeValue($directive, 'allow_output_limit', false)
                        ];

                        foreach ($directive->childNodes as $directiveNode) {
                            if ($directiveNode->nodeType != XML_ELEMENT_NODE) {
                                continue;
                            }
                            switch ($directiveNode->nodeName) {
                            case 'label':
                            case 'param':
                                $directiveData[$directiveNode->nodeName] = $directiveNode->nodeValue;
                                break;
                            case 'renderer':
                                $directiveData[$directiveNode->nodeName] = $this->getAttributeValue($directiveNode, 'type');
                                break;
                            case 'formatters':
                                $directiveData['formatters'] = $this->getDirectiveFormatters($directiveNode);
                                break;
                            case 'mappers':
                                $directiveData['mappers'] = array_merge($directiveData['mappers'], $this->getDirectiveMappers($directiveNode));
                                break;
                            }
                        }
                        $data['directives'][$name] = $directiveData;
                    }
                    break;
                case 'default_product_columns':
                    $columns = isset($data['default_feed_config']['columns']['product_columns']) ?
                        $data['default_feed_config']['columns']['product_columns'] : [];
                    $data['default_feed_config']['columns']['product_columns']
                        = array_merge($columns, $this->getDefaultColumns($childNode));
                    break;
                case 'default_feed_config':
                    $data['default_feed_config'] = array_merge($this->getDefaultConfig($childNode), $data['default_feed_config']);
                    break;
                }
            }
            $output['feed'][$typeName] = $data;
        }

        return $output;
    }

    /**
     * @param  \DOMNode $node
     * @return array
     */
    private function getDirectiveMappers(\DOMNode $node)
    {
        $data = [];
        $filter = $this->getAttributeValue($node, 'filter', false);
        foreach ($node->childNodes as $mapper) {
            if ($mapper->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $forList = $this->getAttributeValue($mapper, 'for', 'default');
            $forList = explode(' ', $forList);
            $type = $this->getAttributeValue($mapper, 'type');
            $configs = [];
            foreach ($mapper->childNodes as $configuration) {
                if ($configuration->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $key = $this->getAttributeValue($configuration, 'name');
                $configs[$key] = $configuration->nodeValue;
            }
            foreach ($forList as $for) {
                $data[$for] = [
                    'type' => $type,
                    'filter' => $filter,
                    'configuration' => $configs
                ];
            }
        }
        return $data;
    }

    /**
     * @param  \DOMNode $node
     * @return array
     */
    private function getDirectiveFormatters(\DOMNode $node)
    {
        $data = [];
        foreach ($node->childNodes as $mapper) {
            if ($mapper->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $forList = $this->getAttributeValue($mapper, 'for', 'default');
            $forList = explode(' ', $forList);
            $type = $this->getAttributeValue($mapper, 'type');
            foreach ($forList as $for) {
                $data[$for] = ['type' => $type];
            }
        }
        return $data;
    }

    /**
     * @param  \DOMNode $node
     * @return array
     */
    private function getDefaultColumns(\DOMNode $node)
    {
        $data = [];
        foreach ($node->childNodes as $column) {
            if ($column->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $name = $this->getChildNodeValue($column, 'column');
            $data[$name] = [
                'column'    => $this->getChildNodeValue($column, 'column'),
                'attribute' => $this->getAttributeValue($column, 'attribute'),
                'order'     => $this->getChildNodeValue($column, 'order'),
                'param'     => $this->getChildNodeValue($column, 'param')
            ];
        }
        return $data;
    }

    /**
     * @param  \DOMNode $node
     * @return array
     */
    private function getDefaultConfig(\DOMNode $node)
    {
        $data = [];
        foreach ($node->childNodes as $group) {
            if ($group->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $name = $group->nodeName;
            $data[$name] = isset($data[$name]) ? $data[$name] : [];

            foreach ($group->childNodes as $config) {
                if ($config->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                if (!isset($data[$name][$config->nodeName])) {
                    $data[$name][$config->nodeName] = $this->decodeValue($config->nodeValue);
                }
            };
        }
        return $data;
    }

    /**
     * Get attribute value
     *
     * @param  \DOMNode    $input
     * @param  string      $attributeName
     * @param  string|null $default
     * @return null|string
     */
    private function getAttributeValue(\DOMNode $input, $attributeName, $default = null)
    {
        $node = $input->attributes->getNamedItem($attributeName);
        return $node ? $node->nodeValue : $default;
    }

    /**
     * Get child node value by child node name
     *
     * @param  \DOMNode $input
     * @param  $nodeName
     * @param  null     $default
     * @return null
     */
    private function getChildNodeValue(\DOMNode $input, $nodeName, $default = null)
    {
        $nodeList = $input->getElementsByTagName($nodeName);

        $nodeValue = $nodeList->length ? $nodeList->item(0)->nodeValue : $default;

        $nodeValue = $this->decodeValue($nodeValue);

        return $nodeValue;
    }

    private function decodeValue($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $startsWith = strlen($value) ? $value[0] : '';

        if (in_array($startsWith, ['[', '{']) && ($newValue = $this->jsonDecoder->decode($value)) !== false) {
            $value = $newValue;
        }

        return $value;
    }
}
