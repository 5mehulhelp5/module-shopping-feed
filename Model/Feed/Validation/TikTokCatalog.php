<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Feed\Validation;

/** Validate formatted rows for the TikTok Ads Manager product catalog. */
class TikTokCatalog
{
    private const REQUIRED = [
        'sku_id', 'title', 'description', 'availability', 'condition', 'price', 'link', 'image_link', 'brand',
    ];
    private const ENUMS = [
        'availability' => ['in stock', 'available for order', 'preorder', 'out of stock', 'discontinued'],
        'condition' => ['new', 'refurbished', 'used'],
        'gender' => ['male', 'female', 'unisex'],
        'age_group' => ['newborn', 'infant', 'toddler', 'kids', 'adult'],
    ];

    /**
     * Return errors preventing export and warnings requiring merchant review.
     *
     * @param array $row
     * @return array{errors: string[], warnings: string[]}
     */
    public function validate(array $row): array
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
        foreach (self::ENUMS as $field => $values) {
            if (($row[$field] ?? '') !== '' && !in_array($row[$field], $values, true)) {
                $errors[] = sprintf('%s must be one of: %s', $field, implode(', ', $values));
            }
        }
        $limits = ['title' => 150, 'description' => 20000];
        for ($i = 0; $i < 5; $i++) {
            $limits['custom_label_' . $i] = 100;
        }
        foreach ($limits as $field => $limit) {
            if (mb_strlen($row[$field] ?? '', 'UTF-8') > $limit) {
                $errors[] = sprintf('%s exceeds %d characters', $field, $limit);
            }
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
        }
        foreach (['link', 'image_link', 'video_link'] as $field) {
            if ($field === 'video_link' && ($row[$field] ?? '') === '') {
                continue;
            }
            if (!$this->validUrl($row[$field] ?? '', $field === 'image_link')) {
                $errors[] = sprintf('%s must be an absolute HTTP(S) URL%s', $field,
                    $field === 'image_link' ? ' with a JPG, JPEG, or PNG path' : '');
            }
        }
        if (($row['additional_image_link'] ?? '') !== '') {
            $images = array_map('trim', explode(',', $row['additional_image_link']));
            if (count($images) > 10 || array_filter($images, fn($url) => !$this->validUrl($url, true))) {
                $errors[] = 'additional_image_link must contain up to 10 comma-separated JPG, JPEG, or PNG URLs';
            }
        }
        foreach (['google_product_category', 'product_type'] as $field) {
            if (count(explode('>', $row[$field] ?? '')) > 3) {
                $errors[] = sprintf('%s supports at most three category levels', $field);
            }
        }
        if (($row['gtin'] ?? '') !== '' && !preg_match('/^(?:\d{8}|\d{12,14})$/D', $row['gtin'])) {
            $errors[] = 'gtin must be an 8, 12, 13, or 14-digit identifier; convert ISBN-10 to ISBN-13';
        }
        if (($row['gtin'] ?? '') === '' && ($row['mpn'] ?? '') === '') {
            $warnings[] = 'gtin and mpn are empty; map real product identifiers when available';
        }
        if (preg_match('/\p{So}/u', $row['title'] ?? '')) {
            $warnings[] = 'Review title symbols and remove emoji before importing into TikTok';
        }
        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /** Validate links locally without fetching merchant resources. */
    private function validUrl(string $value, bool $image): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false
            && preg_match('#^https?://#i', $value)
            && !str_contains($value, '|')
            && (!$image || preg_match('/\.(?:jpe?g|png)$/iD', (string)parse_url($value, PHP_URL_PATH)));
    }

    /** Accept ISO timestamps with or without an explicit timezone, as documented by TikTok. */
    private function validSaleInterval(string $value): bool
    {
        $parts = explode('/', $value);
        if (count($parts) !== 2) {
            return false;
        }
        $dates = [];
        foreach ($parts as $part) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{1,2}:\d{2}(?::\d{2})?(?:Z|[+-]\d{2}:\d{2})?$/D', $part)) {
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
