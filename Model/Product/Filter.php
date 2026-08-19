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

namespace MageOS\ShoppingFeed\Model\Product;

class Filter
{
    /**
     * Feed object
     *
     * @var \MageOS\ShoppingFeed\Model\Feed
     */
    protected $feed;

    /**
     * @var \MageOS\ShoppingFeed\Model\Generator\Cache
     */
    protected $cache;

    public function __construct(
        \MageOS\ShoppingFeed\Model\Generator\Cache $cache
    ) {

        $this->cache = $cache;
    }

    /**
     * Setter for Feed
     *
     * @param  $feed
     * @return $this
     */
    public function setFeed(\MageOS\ShoppingFeed\Model\Feed $feed)
    {
        $this->feed = $feed;
        return $this;
    }

    /**
     * Cleans field.
     *
     * @param  string $field
     * @return string
     */
    public function cleanField($field, $params = null)
    {
        if (!is_null($params) && array_key_exists('column', $params)) {
            $this->findAndReplace($field, $params['column']);
            $this->limitOutput($field, $params['column']);
        }

        $charset = $this->feed->getConfig('output_params_encoding', "UTF-8");
        if (extension_loaded('mbstring')) {
            $supportedEncodings = array_map('strtolower', mb_list_encodings());
            if (!is_string($charset) || !in_array(strtolower($charset), $supportedEncodings, true)) {
                $charset = 'UTF-8';
            }
            $sourceEncoding = mb_detect_encoding($field, mb_detect_order(), true);
            if ($sourceEncoding !== false) {
                $field = mb_convert_encoding($field, $charset, $sourceEncoding);
            }
        }

        $delimiter = (string)$this->feed->getConfig('output_params_delimiter', "\t");
        $delimiter_other = (string)$this->feed->getConfig('output_params_delimiter_other', "\t");
        $replacements = [
            "\"" => "&quot;",
            "'" => "&#39;",
            "’" => "&#8217;",
            "‘" => "&#8216;",
            "\n" => " ",
            "\r" => " ",
        ];
        $activeDelimiter = $delimiter == 'other' ? $delimiter_other : ($delimiter == '\t' ? "\t" : $delimiter);
        if ($activeDelimiter !== '') {
            $replacements[$activeDelimiter] = ' ';
        }
        $field = strtr($field, $replacements);

        $field = strip_tags($field, '>');
        if (extension_loaded('mbstring')) {
            $field = preg_replace_callback("/(&#?[a-z0-9]{2,8};)/i", [$this, 'htmlEntitiesToUtf8Callback'], $field);
        }
        $field = preg_replace('/\s\s+/', ' ', $field);
        $field = str_replace(PHP_EOL, "", $field);
        $field = trim($field);

        return $field;
    }

    /**
     * Find a replace logic
     *
     * @param $string
     * @param $column
     */
    public function findAndReplace(&$string, $column)
    {
        $cacheKey = ['feed', $this->feed->getId(), 'find_and_replace'];
        if ($this->cache->getCache($cacheKey, true)) {
            $def = ['find' => [], 'replace' => []];
            $findAndReplace = ['-all-' => $def];

            $filterData = $this->feed->getConfig('filters_find_and_replace');

            if (is_array($filterData) && count($filterData) > 0) {
                foreach ($filterData as $item) {
                    if (empty($item['column'])) {
                        array_push($findAndReplace['-all-']['find'], $item['find']);
                        array_push($findAndReplace['-all-']['replace'], $item['replace']);
                    } else {
                        if (!array_key_exists($item['column'], $findAndReplace)) {
                            $findAndReplace[$item['column']] = $def;
                        }
                        array_push($findAndReplace[$item['column']]['find'], $item['find']);
                        array_push($findAndReplace[$item['column']]['replace'], $item['replace']);
                    }
                }
            }
            $this->cache->setCache($cacheKey, $findAndReplace);
        }
        $findAndReplace = $this->cache->getCache($cacheKey, []);

        if (array_key_exists((string)$column, $findAndReplace)) {
            $string = str_replace($findAndReplace[$column]['find'], $findAndReplace[$column]['replace'], $string);
        }
        if (!empty($findAndReplace['-all-']['find'])) {
            $string = str_replace($findAndReplace['-all-']['find'], $findAndReplace['-all-']['replace'], $string);
        }
    }

    /**
     * Truncate $string by column limit
     *
     * @param $string
     * @param $column
     */
    public function limitOutput(&$string, $column)
    {
        $limitData = $this->feed->getConfig('filters_output_limit');
        if (!is_null($limitData)) {
            foreach ($limitData as $data) {
                $limit = intval($data['limit']);
                if ($data['column'] == $column) {
                    if (strlen($string) > $limit) {
                        $string = substr($string, 0, $limit);
                    }
                    continue;
                }
            }
        }
    }

    /**
     * @param  $matches
     * @return string
     */
    public function htmlEntitiesToUtf8Callback($matches)
    {
        return mb_convert_encoding($matches[1], "UTF-8", "HTML-ENTITIES");
    }
}
