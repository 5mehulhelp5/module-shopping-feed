<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Model\Feed\Validation;

/** Validate the stable OpenAI Google-compatible file profile, not the OpenAI native schema. */
class OpenAiGoogleCompatible
{
    private const REQUIRED = ['id', 'title', 'description', 'link', 'image_link', 'availability', 'price', 'brand'];
    private const ENUMS = [
        'availability' => ['in_stock', 'out_of_stock', 'preorder', 'backorder'],
        'condition' => ['new', 'refurbished', 'used'],
        'identifier_exists' => ['yes', 'true', 'no', 'false'],
        'gender' => ['male', 'female', 'unisex'],
        'age_group' => ['newborn', 'infant', 'toddler', 'kids', 'adult'],
        'size_system' => ['US', 'UK', 'EU', 'DE', 'FR', 'JP', 'CN', 'IT', 'BR', 'MEX', 'AU'],
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
            if (($value !== null && !is_scalar($value)) || (is_bool($value) && $field !== 'identifier_exists')) {
                $errors[] = sprintf('%s must contain one non-boolean cell value', $field);
            } elseif (!mb_check_encoding((string)$value, 'UTF-8')
                || preg_match('/[\p{Cc}\p{Cf}]/u', (string)$value)
            ) {
                $errors[] = sprintf('%s must contain valid UTF-8 without control or formatting characters', $field);
            }
        }
        if ($errors) {
            return ['errors' => $errors, 'warnings' => []];
        }
        $row = array_map(static fn($value) => is_bool($value) ? ($value ? 'true' : 'false') : trim((string)$value), $row);
        foreach (self::REQUIRED as $field) {
            if (($row[$field] ?? '') === '' || in_array(strtolower($row[$field]), ['unknown', 'null', 'n/a'], true)) {
                $errors[] = sprintf('%s requires a real, nonempty value', $field);
            }
        }
        foreach (self::ENUMS as $field => $values) {
            if (($row[$field] ?? '') !== '' && !in_array($row[$field], $values, true)) {
                $errors[] = sprintf('%s must be one of: %s', $field, implode(', ', $values));
            }
        }
        foreach (['title' => 150, 'description' => 5000] as $field => $limit) {
            if (mb_strlen($row[$field] ?? '', 'UTF-8') > $limit
                || strip_tags($row[$field] ?? '') !== ($row[$field] ?? '')
            ) {
                $errors[] = sprintf('%s must contain plain text of at most %d characters', $field, $limit);
            }
        }
        foreach (['link', 'image_link'] as $field) {
            if (!$this->validUrl($row[$field] ?? '')) {
                $errors[] = sprintf('%s must be an absolute HTTP(S) URL without credentials', $field);
            }
        }
        if (($row['additional_image_link'] ?? '') !== '') {
            foreach (explode(',', $row['additional_image_link']) as $url) {
                if (!$this->validUrl(trim($url))) {
                    $errors[] = 'additional_image_link must contain comma-separated HTTP(S) URLs without credentials';
                    break;
                }
            }
        }
        $group = $row['item_group_id'] ?? '';
        if (($requiresItemGroupId && $group === '') || ($group !== '' && $group === ($row['id'] ?? ''))) {
            $errors[] = 'item_group_id is required for variants and must differ from id';
        }
        $gtin = str_replace([' ', '-'], '', $row['gtin'] ?? '');
        $validGtin = $this->validGtin($gtin);
        if ($gtin !== '' && !$validGtin) {
            $errors[] = 'gtin must have 8, 12, 13, or 14 digits and a valid check digit';
        }
        if ($gtin !== '' && preg_match('/^(?:02|04|2|05|98|99)/', $gtin)) {
            $validGtin = false;
            $warnings[] = 'gtin uses a restricted prefix and will not be retained by OpenAI';
        }
        if (!$validGtin && ($row['mpn'] ?? '') === ''
            && !in_array($row['identifier_exists'] ?? '', ['no', 'false'], true)
        ) {
            $errors[] = 'a valid gtin or mpn is required unless identifier_exists is explicitly no or false';
        }
        $date = $row['availability_date'] ?? '';
        if (($date !== '' && $this->parseDate($date) === null)
            || ($date === '' && in_array($row['availability'] ?? '', ['preorder', 'backorder'], true))
        ) {
            $errors[] = 'availability_date requires a valid date or timestamp with timezone for preorder and backorder';
        }
        if (($row['expiration_date'] ?? '') !== '' && $this->parseDate($row['expiration_date'], false) === null) {
            $errors[] = 'expiration_date must be an ISO 8601 timestamp with timezone';
        }
        $price = $row['price'] ?? '';
        if (!$this->validMoney($price)) {
            $errors[] = 'price must contain a nonnegative amount with two decimals and an uppercase currency code';
        } elseif ((float)$price === 0.0) {
            if (!in_array($row['google_product_category'] ?? '', ['267', '4745'], true)) {
                $errors[] = 'zero price requires google_product_category ID 267 or 4745';
            }
            if (!preg_match('/^(?:month|year):[1-9]\d*:(\d+\.\d{2} [A-Z]{3})$/D', $row['subscription_cost'] ?? '', $matches)
                || (float)$matches[1] <= 0 || substr($matches[1], -3) !== substr($price, -3)
            ) {
                $errors[] = 'zero price requires subscription_cost with positive periods and amount in the price currency';
            }
        }
        $sale = $row['sale_price'] ?? '';
        if ($sale !== '' && (!$this->validMoney($sale) || (float)$sale <= 0 || (float)$sale >= (float)$price
            || substr($sale, -3) !== substr($price, -3))
        ) {
            $errors[] = 'sale_price must be positive, below price, and in the same currency';
        }
        if (($row['sale_price_effective_date'] ?? '') !== '') {
            $parts = explode('/', $row['sale_price_effective_date']);
            $start = $this->parseDate($parts[0]);
            $end = count($parts) === 2 ? $this->parseDate($parts[1], true, true) : null;
            if ($sale === '' || $start === null || $end === null || $start >= $end) {
                $errors[] = 'sale_price_effective_date requires sale_price and a valid ISO 8601 start/end interval';
            }
        }
        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /** Reject embedded credentials even if the URL is otherwise well formed. */
    private function validUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false
            && preg_match('#^https?://#i', $value)
            && parse_url($value, PHP_URL_USER) === null
            && parse_url($value, PHP_URL_PASS) === null
            && !str_contains($value, '|');
    }

    /** Keep prices at the compatibility profile's supported precision. */
    private function validMoney(string $value): bool
    {
        return (bool)preg_match('/^\d+\.\d{2} [A-Z]{3}$/D', $value);
    }

    /** Check the assigned GTIN's length and GS1 modulo-10 check digit. */
    private function validGtin(string $value): bool
    {
        if (!preg_match('/^(?:\d{8}|\d{12,14})$/D', $value)) {
            return false;
        }
        $sum = 0;
        for ($i = strlen($value) - 2, $weight = 3; $i >= 0; $i--, $weight = 4 - $weight) {
            $sum += (int)$value[$i] * $weight;
        }
        return (10 - $sum % 10) % 10 === (int)substr($value, -1);
    }

    /** Date-only sale endpoints cover the full UTC day; timestamps must declare their timezone. */
    private function parseDate(string $value, bool $allowDateOnly = true, bool $endOfDay = false): ?\DateTimeImmutable
    {
        $dateOnly = (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value);
        if ($dateOnly && !$allowDateOnly) {
            return null;
        }
        if (!$dateOnly && !preg_match('/^\d{4}-\d{2}-\d{2}T(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?(?:Z|[+-](?:[01]\d|2[0-3]):?[0-5]\d)$/D', $value)) {
            return null;
        }
        try {
            $date = new \DateTimeImmutable($value, new \DateTimeZone('UTC'));
            $errors = \DateTimeImmutable::getLastErrors();
            if ($errors && ($errors['warning_count'] || $errors['error_count'])) {
                return null;
            }
            return $dateOnly && $endOfDay ? $date->setTime(23, 59, 59) : $date;
        } catch (\Exception $exception) {
            return null;
        }
    }
}
