<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit;

use PHPUnit\Framework\MockObject\Stub\ConsecutiveCalls;
use PHPUnit\Framework\MockObject\Stub\ReturnArgument;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use PHPUnit\Framework\MockObject\Stub\ReturnSelf;
use PHPUnit\Framework\MockObject\Stub\ReturnStub;
use PHPUnit\Framework\MockObject\Stub\ReturnValueMap;
use PHPUnit\Framework\TestCase;

abstract class PhpUnit12CompatibilityTestCase extends TestCase
{
    use CompatibilityMockTrait;

    public static function returnValue($value): ReturnStub
    {
        return new ReturnStub($value);
    }

    public static function returnSelf(): ReturnSelf
    {
        return new ReturnSelf();
    }

    public static function returnArgument(int $argumentIndex): ReturnArgument
    {
        return new ReturnArgument($argumentIndex);
    }

    public static function returnCallback($callback): ReturnCallback
    {
        return new ReturnCallback($callback);
    }

    public static function onConsecutiveCalls(...$args): ConsecutiveCalls
    {
        return new ConsecutiveCalls($args);
    }

    public static function returnValueMap(array $valueMap): ReturnValueMap
    {
        return new ReturnValueMap($valueMap);
    }
}
