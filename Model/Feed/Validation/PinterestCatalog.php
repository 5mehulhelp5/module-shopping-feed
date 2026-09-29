<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Feed\Validation;

/** Validate formatted rows for the Pinterest retail catalog. */
class PinterestCatalog
{
    private const REQUIRED = [
        'id', 'title', 'description', 'availability', 'price', 'link', 'image_link',
    ];
    private const ENUMS = [
        'availability' => ['in stock', 'out of stock', 'preorder'],
        'condition' => ['new', 'refurbished', 'used'],
        'gender' => ['male', 'female', 'unisex'],
        'age_group' => ['newborn', 'infant', 'toddler', 'kids', 'adult'],
    ];

    /**
     * Return errors preventing export and warnings requiring merchant review.
     *
     * @param array $row
     * @param bool $requiresItemGroupId
     * @return array{errors: string[], warnings: string[]}
     */
    public function validate(array $row, bool $requiresItemGroupId = false): array
    {
        $errors = [];
        $warnings = [];
        foreach ($row as $field => $value) {
            if ($value !== null && !is_scalar($value)) {
                $errors[] = sprintf('%s must contain one cell value', $field);
            } elseif (!mb_check_encoding((string)$value, 'UTF-8')) {
                $errors[] = sprintf('%s must contain valid UTF-8', $field);
            } elseif (preg_match('/[\p{Cc}\p{Cf}]/u', (string)$value)) {
                $errors[] = sprintf('%s must not contain control or formatting characters', $field);
            }
        }
        if ($errors) {
            return ['errors' => $errors, 'warnings' => []];
        }
        $row = array_map(static fn($value) => trim((string)$value), $row);
        foreach (self::REQUIRED as $field) {
            if (($row[$field] ?? '') === '') {
                $errors[] = sprintf('%s is required and must contain one value', $field);
            }
        }
        if (($requiresItemGroupId || ($row['variant_names'] ?? '') !== '' || ($row['variant_values'] ?? '') !== '')
            && ($row['item_group_id'] ?? '') === ''
        ) {
            $errors[] = 'item_group_id is required for product variants';
        }
        foreach (self::ENUMS as $field => $values) {
            if (($row[$field] ?? '') !== '' && !in_array($row[$field], $values, true)) {
                $errors[] = sprintf('%s must be one of: %s', $field, implode(', ', $values));
            }
        }
        $limits = [
            'id' => 127, 'item_group_id' => 127, 'title' => 500, 'description' => 10000,
            'link' => 511, 'image_link' => 2000, 'video_link' => 2000, 'brand' => 100, 'mpn' => 70,
            'color' => 30, 'size' => 30, 'material' => 30, 'pattern' => 30, 'product_type' => 1000,
        ];
        for ($i = 0; $i < 5; $i++) {
            $limits['custom_label_' . $i] = 511;
        }
        foreach ($limits as $field => $limit) {
            if (mb_strlen($row[$field] ?? '', 'UTF-8') > $limit) {
                $errors[] = sprintf('%s exceeds %d characters', $field, $limit);
            }
        }
        if (strip_tags($row['description'] ?? '') !== ($row['description'] ?? '')) {
            $errors[] = 'description must contain plain text, not HTML';
        }
        foreach (['price', 'sale_price'] as $field) {
            if ($field === 'sale_price' && ($row[$field] ?? '') === '') {
                continue;
            }
            $value = $row[$field] ?? '';
            if (!preg_match('/^\d+\.\d{2} [A-Z]{3}$/D', $value) || (float)$value <= 0) {
                $errors[] = sprintf('%s must be a positive amount with a currency code, for example 29.99 USD', $field);
            }
        }
        if (($row['sale_price'] ?? '') !== '') {
            if (substr($row['sale_price'], -3) !== substr($row['price'] ?? '', -3)) {
                $errors[] = 'sale_price must use the same currency as price';
            }
            if ((float)$row['sale_price'] < (float)($row['price'] ?? 0)
                && ($row['sale_price_effective_date'] ?? '') !== ''
                && !$this->validSaleInterval($row['sale_price_effective_date'])
            ) {
                $errors[] = 'sale_price_effective_date must contain an ISO 8601 start/end interval';
            }
            if ((float)$row['sale_price'] < (float)($row['price'] ?? 0)
                && ($row['sale_price_effective_date'] ?? '') === ''
            ) {
                $warnings[] = 'sale_price has no effective date; keep the feed current when the offer ends';
            }
        }
        foreach (['link', 'image_link', 'mobile_link', 'ad_link', 'video_link'] as $field) {
            if (!in_array($field, ['link', 'image_link'], true) && ($row[$field] ?? '') === '') {
                continue;
            }
            if (!$this->validUrl($row[$field] ?? '')
                || ($field === 'image_link' && str_contains($row[$field] ?? '', ','))
            ) {
                $errors[] = sprintf('%s must be an absolute HTTP(S) URL; encode commas in image URLs', $field);
            }
        }
        if (($row['video_link'] ?? '') !== ''
            && !preg_match('/\.(?:mp4|mov|m4v)$/iD', (string)parse_url($row['video_link'], PHP_URL_PATH))
        ) {
            $errors[] = 'video_link must use an MP4, MOV, or M4V path';
        }
        if (($row['additional_image_link'] ?? '') !== '') {
            $images = array_map('trim', explode(',', $row['additional_image_link']));
            if (count($images) > 10 || array_filter($images, fn($url) => !$this->validUrl($url) || mb_strlen($url) > 2000)) {
                $errors[] = 'additional_image_link must contain up to 10 comma-separated HTTP(S) URLs of at most 2000 characters each';
            }
        }
        $category = $row['product_type'] ?? '';
        if (count(explode('>', $category)) > 5 || preg_match('/(?<! )>|>(?! )/', $category)) {
            $errors[] = 'product_type supports at most five levels separated by space, >, space';
        }
        $names = ($row['variant_names'] ?? '') === '' ? [] : array_map('trim', explode(',', $row['variant_names']));
        $values = ($row['variant_values'] ?? '') === '' ? [] : array_map('trim', explode(',', $row['variant_values']));
        if (count($names) !== count($values) || in_array('', $names, true) || in_array('', $values, true)) {
            $errors[] = 'variant_names and variant_values must contain matching nonempty comma-separated lists';
        }
        if (($row['gtin'] ?? '') !== '' && !preg_match('/^(?:\d{8}|\d{12,14})$/D', $row['gtin'])) {
            $errors[] = 'gtin must be an 8, 12, 13, or 14-digit identifier; convert ISBN-10 to ISBN-13';
        }
        if (($row['gtin'] ?? '') === '' && ($row['mpn'] ?? '') === '') {
            $warnings[] = 'gtin and mpn are empty; map real product identifiers when available';
        }
        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /** Validate links locally without fetching merchant resources. */
    private function validUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false
            && preg_match('#^https?://#i', $value)
            && !str_contains($value, '|');
    }

    /** Accept ISO timestamps with or without an explicit timezone, including fractional seconds. */
    private function validSaleInterval(string $value): bool
    {
        $parts = explode('/', $value);
        if (count($parts) !== 2) {
            return false;
        }
        $dates = [];
        foreach ($parts as $part) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}(?:T\d{2}:\d{2}(?::\d{2}(?:\.\d{1,6})?)?(?:Z|[+-]\d{2}:?\d{2})?)?$/D', $part)) {
                return false;
            }
            try {
                $dates[] = new \DateTimeImmutable($part, new \DateTimeZone('UTC'));
                $errors = \DateTimeImmutable::getLastErrors();
                if ($errors && ($errors['warning_count'] || $errors['error_count'])) {
                    return false;
                }
            } catch (\Exception $exception) {
                return false;
            }
        }
        return $dates[0] < $dates[1];
    }
}
