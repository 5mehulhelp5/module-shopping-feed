<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Ui\DataProvider\Feed\Form;

use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\FeedTypes\Config;

/** Declarative replacements for the bundled PHP parameter renderers. */
class Parameters
{
    public function __construct(private Config $types, private Options $options, private array $renderers = [])
    {
    }

    public function get(Feed $feed): array
    {
        $result = [];
        foreach ($this->types->getDirectives($feed->getType()) as $code => $directive) {
            $renderer = $directive['renderer'] ?? '';
            $renderer = is_array($renderer) ? ($renderer['type'] ?? '') : $renderer;
            $definition = $this->renderers[$renderer] ?? [
                'kind' => 'unsupported',
                'notice' => __('This custom parameter is preserved. Its extension needs a UI component parameter definition to edit it.')
            ];
            if ($renderer === '') {
                $definition = ['kind' => 'none'];
            }
            if (isset($definition['source'])) {
                $definition['options'] = $this->options->get($definition['source']);
                unset($definition['source']);
            }
            $definition['default'] = $directive['param'] ?? '';
            if ($definition['kind'] === 'none') {
                $definition['notice'] = trim(html_entity_decode(strip_tags($directive['param'] ?? ''), ENT_QUOTES, 'UTF-8'));
            }
            foreach (['label', 'notice'] as $key) {
                if (isset($definition[$key]) && is_string($definition[$key])) {
                    $definition[$key] = __($definition[$key]);
                }
            }
            $result[$code] = $definition;
        }
        return $result;
    }
}
