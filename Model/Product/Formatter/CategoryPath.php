<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Product\Formatter;

/** Limit a single category path to the configured destination depth. */
class CategoryPath extends FormatterAbstract
{
    /** @param int $maxLevels */
    public function __construct(private readonly int $maxLevels = 3)
    {
    }

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
        return implode(' > ', array_slice(array_map('trim', explode('>', $var)), 0, $this->maxLevels));
    }
}
