<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit;

use PHPUnit\Framework\TestCase;

$compatibilityClass = method_exists(TestCase::class, 'returnValue')
    ? LegacyCompatibilityTestCase::class
    : PhpUnit12CompatibilityTestCase::class;

class_alias($compatibilityClass, __NAMESPACE__ . '\\CompatibilityTestCase');
