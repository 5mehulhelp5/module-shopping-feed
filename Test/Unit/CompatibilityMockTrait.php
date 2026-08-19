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
     * @param class-string $className
     * @param list<string> $methods
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
            $registration = new \ReflectionMethod(TestCase::class, 'registerMockObject');
            if ($registration->getNumberOfParameters() === 1) {
                $this->registerMockObject($mock);
            } else {
                $this->registerMockObject($className, $mock);
            }

            return $mock;
        }

        $builder = $this->getMockBuilder($className)->disableOriginalConstructor();

        if ($methods !== []) {
            $builder->setMethods($methods);
        }

        return $builder->getMock();
    }
}
