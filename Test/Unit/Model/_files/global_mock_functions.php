<?php

// @codingStandardsIgnoreFile

namespace MageOS\ShoppingFeed\Model\Generator;

use MageOS\ShoppingFeed\Test\Unit\Model\Generator\MemoryTest;

function ini_get($key)
{
    switch($key) {
        case 'memory_limit':
            if (MemoryTest::$returnNormalLimit) {
                return -1;
            }
            return '1E';
        case 'max_execution_time':
            return 5;
        default:
            throw new \Exception('unknown ini_get param received: '.$key);
    }
}

function memory_get_usage($usage)
{
    if (MemoryTest::$returnNormalLimit) {
        return 100;
    }
    return 1000000000000000000000;
}