<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Ui\DataProvider\Feed\Form;

use MageOS\ShoppingFeed\Model\Feed;
use MageOS\ShoppingFeed\Model\Promotions\Provider;
use MageOS\ShoppingFeed\Model\Promotions\Provider\Collection;

class Promotions
{
    public function __construct(private Provider $provider, private Collection $collection)
    {
    }

    public function rules(Feed $feed)
    {
        return $this->collection->getPromotionRules($feed);
    }

    public function data(Feed $feed, array $data): array
    {
        $this->provider->setFeed($feed);
        $widget = (array)($data['config']['promotions_provider_widget'] ?? []);
        $widget += ['counter' => 0, 'promotion' => []];
        foreach ($this->rules($feed) as $rule) {
            $id = $rule->getId();
            $row = $widget['promotion'][$id] ?? [];
            $from = $this->provider->prepareDate($rule->getFromDate(), $row['date']['from'] ?? '');
            $to = $this->provider->prepareDate($rule->getToDate(), $row['date']['to'] ?? '', $from);
            $row['date'] = ['from' => $from, 'to' => $to];
            foreach (['from' => $from, 'to' => $to] as $boundary => $default) {
                $row['display'][$boundary] = isset($row['display'][$boundary])
                    ? $this->provider->prepareDate('', $row['display'][$boundary]) : $default;
            }
            $widget['promotion'][$id] = array_replace_recursive([
                'include' => 0, 'title' => $rule->getName(),
                'date' => ['from' => $from, 'to' => $to], 'display' => ['from' => $from, 'to' => $to]
            ], $row);
        }
        $data['config']['promotions_provider_widget'] = $widget;
        return $data;
    }
}
