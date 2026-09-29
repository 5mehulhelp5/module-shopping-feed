<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Feed\Validation;

/** Validate the Microsoft tab-delimited product preset before writing each row. */
class MicrosoftMerchantCenter
{
    private const REQUIRED = ['id', 'title', 'description', 'link', 'image_link', 'price', 'availability', 'condition'];
    private const LIMITS = [
        'id' => 50, 'item_group_id' => 50, 'title' => 150, 'description' => 10000,
        'link' => 2000, 'image_link' => 2000, 'brand' => 1000, 'mpn' => 70,
        'color' => 100, 'size' => 100, 'material' => 200, 'pattern' => 100,
        'product_category' => 255, 'product_type' => 750,
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
            } elseif (preg_match('/[\t\r\n]/', (string)$value)) {
                $errors[] = sprintf('%s must not contain tabs or line breaks', $field);
            }
        }
        if ($errors) {
            return ['errors' => $errors, 'warnings' => []];
        }
        $row = array_map(static fn($value) => trim((string)$value), $row);
        foreach (self::REQUIRED as $field) {
            if (($row[$field] ?? '') === '') {
                $errors[] = sprintf('%s is required by this preset', $field);
            }
        }
        foreach (self::LIMITS as $field => $limit) {
            if (mb_strlen($row[$field] ?? '', 'UTF-8') > $limit) {
                $errors[] = sprintf('%s exceeds %d characters', $field, $limit);
            }
        }
        foreach ([
            'availability' => ['in stock', 'out of stock', 'preorder'],
            'condition' => ['new', 'used', 'refurbished'],
            'gender' => ['male', 'female', 'unisex'],
            'age_group' => ['newborn', 'infant', 'toddler', 'kids', 'adult'],
            'identifier_exists' => ['TRUE', 'FALSE'],
        ] as $field => $values) {
            if (($row[$field] ?? '') !== '' && !in_array($row[$field], $values, true)) {
                $errors[] = sprintf('%s must be one of: %s', $field, implode(', ', $values));
            }
        }
        foreach (['link', 'image_link'] as $field) {
            if (!filter_var($row[$field] ?? '', FILTER_VALIDATE_URL)
                || !preg_match('#^https?://#i', $row[$field] ?? '')
            ) {
                $errors[] = sprintf('%s must be an absolute HTTP or HTTPS URL', $field);
            }
        }
        foreach (['price', 'sale_price'] as $field) {
            if ($field === 'sale_price' && ($row[$field] ?? '') === '') {
                continue;
            }
            $value = $row[$field] ?? '';
            $pattern = $field === 'price' ? '/^\d+\.\d{2} [A-Z]{3}$/D' : '/^\d+\.\d{2}$/D';
            if (!preg_match($pattern, $value)
                || (float)$value <= 0 || (float)$value > 10000000
            ) {
                $errors[] = sprintf('%s must be a positive amount up to 10000000.00 with two decimals%s',
                    $field, $field === 'price' ? ' and a currency code' : '');
            }
        }
        if (($row['sale_price'] ?? '') !== '' && (float)$row['sale_price'] < (float)($row['price'] ?? 0)) {
            if (!$this->validSaleInterval($row['sale_price_effective_date'] ?? '')) {
                $errors[] = 'sale_price_effective_date must contain an ISO 8601 start/end interval with time zones';
            }
        }
        if (($row['item_group_id'] ?? '') !== '' && $row['item_group_id'] === ($row['id'] ?? '')) {
            $errors[] = 'item_group_id must differ from id';
        }
        $brand = $row['brand'] ?? '';
        $gtin = $row['gtin'] ?? '';
        $mpn = $row['mpn'] ?? '';
        if (count(preg_split('/\s+/u', $brand, -1, PREG_SPLIT_NO_EMPTY)) > 10) {
            $errors[] = 'brand exceeds 10 words';
        } elseif (mb_strlen($brand, 'UTF-8') > 70) {
            $warnings[] = 'brand exceeds the recommended 70 characters';
        }
        if ($gtin !== '') {
            $gtins = preg_split('/,\s*/', $gtin);
            if (count($gtins) > 10 || array_filter($gtins, static fn($value) =>
                !preg_match('/^(?:\d{8}|\d{12,14}|\d{9}[\dXx])$/D', $value))) {
                $errors[] = 'gtin must contain up to 10 valid-length GTIN or ISBN values separated by commas';
            }
        }
        $hasIdentifiers = $brand !== '' || $gtin !== '' || $mpn !== '';
        if (($row['identifier_exists'] ?? '') === 'FALSE' && $hasIdentifiers) {
            $errors[] = 'identifier_exists cannot be FALSE when brand, gtin, or mpn is supplied';
        } elseif (!$hasIdentifiers && ($row['condition'] ?? 'new') === 'new'
            && ($row['identifier_exists'] ?? '') !== 'FALSE'
        ) {
            $errors[] = 'Map real identifiers or explicitly confirm identifier_exists=FALSE for unassigned identifiers';
        } elseif ($hasIdentifiers && ($brand === '' || ($gtin === '' && $mpn === ''))) {
            $warnings[] = 'Identifier data is incomplete; supply brand, gtin, and mpn when assigned by the manufacturer';
        }
        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Validate a complete dated sale without inventing a period.
     *
     * @param string $value
     * @return bool
     */
    private function validSaleInterval(string $value): bool
    {
        $parts = explode('/', $value);
        if (count($parts) !== 2) {
            return false;
        }
        $dates = [];
        foreach ($parts as $part) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(?::\d{2})?(?:Z|[+-]\d{2}:\d{2})$/D', $part)) {
                return false;
            }
            try {
                $dates[] = new \DateTimeImmutable($part);
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
