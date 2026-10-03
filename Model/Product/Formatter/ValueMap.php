<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Product\Formatter;

/** Translate a directive's internal values to a destination vocabulary. */
class ValueMap extends FormatterAbstract
{
    /**
     * Configure the destination vocabulary.
     *
     * @param array $values
     */
    public function __construct(private array $values = [])
    {
    }

    /**
     * Format known values; leave unsupported values empty for required-field validation.
     *
     * @param mixed $var
     * @return string
     */
    public function run($var)
    {
        return is_string($var) ? ($this->values[$var] ?? '') : '';
    }
}
