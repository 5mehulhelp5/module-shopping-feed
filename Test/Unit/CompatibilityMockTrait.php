<?php

declare(strict_types=1);

namespace MageOS\ShoppingFeed\Test\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

trait CompatibilityMockTrait
{
    /**
     * Build a partial mock that also supports Magento's magic data methods.
     *
     * PHPUnit 10 removed setMethods(). Its generator still accepts explicit
     * virtual method names, which preserves Magento DataObject test doubles.
     *
     * @template T of object
     * @param class-string<T> $className
     * @param list<string> $methods
     * @return T&MockObject
     */
    protected function createCompatibleMock(string $className, array $methods = []): MockObject
    {
        $modernGeneratorClass = 'PHPUnit\\Framework\\MockObject\\Generator\\Generator';
        if (class_exists($modernGeneratorClass)) {
            $generator = new $modernGeneratorClass();
            $generatorMethod = new \ReflectionMethod($generator, 'testDouble');
            $generatorParameters = [
                'type' => $className,
                'mockObject' => true,
                'methods' => $methods,
                'arguments' => [],
                'callOriginalConstructor' => false,
                'callOriginalClone' => false,
                'returnValueGeneration' => true,
            ];
            $parameterNames = array_map(
                static fn (\ReflectionParameter $parameter): string => $parameter->getName(),
                $generatorMethod->getParameters()
            );
            if (in_array('markAsMockObject', $parameterNames, true)) {
                $generatorParameters['markAsMockObject'] = true;
            }
            $mock = $generatorMethod->invokeArgs($generator, $generatorParameters);
            // PHPUnit 12.5 added the class name to registerMockObject().
            $registration = new \ReflectionMethod(TestCase::class, 'registerMockObject');
            $registration->invokeArgs(
                $this,
                $registration->getNumberOfParameters() === 1 ? [$mock] : [$className, $mock]
            );

            return $mock;
        }

        $builder = $this->getMockBuilder($className)->disableOriginalConstructor();

        if ($methods !== []) {
            // setMethods() exists only on the PHPUnit 9 MockBuilder this branch runs on.
            (new \ReflectionMethod($builder, 'setMethods'))->invoke($builder, $methods);
        }

        return $builder->getMock();
    }
}
