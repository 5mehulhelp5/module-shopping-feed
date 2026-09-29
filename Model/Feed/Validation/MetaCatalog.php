<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Feed\Validation;

/** Validate formatted Meta rows for both file generation and Test Feed. */
class MetaCatalog
{
    private const REQUIRED = ['id', 'title', 'description', 'availability', 'condition', 'price', 'link', 'image_link', 'brand'];
    private const AVAILABILITY = ['in stock', 'out of stock'];

    /**
     * Return errors that prevent export and warnings that need merchant review.
     *
     * @param array $row
     * @return array{errors: string[], warnings: string[]}
     */
    public function validate(array $row): array
    {
        $errors = [];
        foreach (self::REQUIRED as $field) {
            if (!is_scalar($row[$field] ?? null) || trim((string)$row[$field]) === '') {
                $errors[] = sprintf('%s is required and must contain one value', $field);
            }
        }
        if (!in_array($row['condition'] ?? '', ['new', 'refurbished', 'used'], true)) {
            $errors[] = 'condition must be new, refurbished, or used';
        }
        if (!in_array($row['availability'] ?? '', self::AVAILABILITY, true)) {
            $errors[] = 'availability must be in stock or out of stock';
        }
        foreach (['price', 'sale_price'] as $field) {
            if ($field === 'sale_price' && (!isset($row[$field]) || $row[$field] === '')) {
                continue;
            }
            $value = $row[$field] ?? '';
            if (!is_string($value) || !preg_match('/^\d+\.\d{2} [A-Z]{3}$/D', $value) || (float)$value <= 0) {
                $errors[] = sprintf('%s must be a positive amount with a currency code, for example 29.99 USD', $field);
            }
        }
        if (is_string($row['sale_price'] ?? null) && $row['sale_price'] !== ''
            && is_string($row['price'] ?? null) && substr($row['sale_price'], -3) !== substr($row['price'], -3)
        ) {
            $errors[] = 'sale_price must use the same currency as price';
        }
        foreach (['link', 'image_link'] as $field) {
            $value = $row[$field] ?? '';
            if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_URL)
                || !preg_match('#^https?://#i', $value)
            ) {
                $errors[] = sprintf('%s must be an absolute HTTP or HTTPS URL', $field);
            }
        }
        $warnings = [];
        if ($this->isBlank($row['gtin'] ?? null) && $this->isBlank($row['mpn'] ?? null)) {
            $warnings[] = 'gtin and mpn are empty; map real product identifiers when available';
        }
        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Check whether an identifier contains a single usable value.
     *
     * @param mixed $value
     * @return bool
     */
    private function isBlank($value): bool
    {
        return !is_scalar($value) || trim((string)$value) === '';
    }
}
