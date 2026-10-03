<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Ui\DataProvider\Feed\Form;

use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Product\Category\CollectionProvider;

/** Option sources are injectable so integrations can extend the form without rendering HTML. */
class Options
{
    private array $cache = [];

    public function __construct(private CollectionProvider $categoryProvider, private array $sources = [])
    {
    }

    public function get(string $name): array
    {
        if (!isset($this->sources[$name])) {
            throw new \InvalidArgumentException('Unknown feed form option source: ' . $name);
        }
        if (!isset($this->cache[$name])) {
            $this->cache[$name] = $this->sources[$name]->toOptionArray();
            if ($name === 'Attributes') {
                array_unshift($this->cache[$name], ['value' => '', 'label' => __('-- Please Select --')]);
            }
            if ($name === 'Delimiter') {
                foreach ($this->cache[$name] as &$option) {
                    if ($option['value'] === "\t") {
                        // The form's required-entry validator trims a literal tab as whitespace.
                        $option['value'] = '\\t';
                    }
                }
                unset($option);
            }
        }
        return $this->cache[$name];
    }

    public function categories(Feed $feed): array
    {
        return array_map(static fn (array $category): array => [
            'value' => (string)$category['id'],
            'label' => str_repeat('  ', max(0, (int)$category['level'] - 1)) . $category['name']
        ], $this->categoryProvider->getCategories($feed));
    }
}
