<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Product\Formatter;

/** Limit a single category path to the three levels used by TikTok catalogs. */
class CategoryPath extends FormatterAbstract
{
    /**
     * Retain the leading hierarchy without changing category names.
     *
     * @param mixed $var
     * @return string
     */
    public function run($var)
    {
        if (!is_string($var) || trim($var) === '') {
            return '';
        }
        return implode(' > ', array_slice(array_map('trim', explode('>', $var)), 0, 3));
    }
}
