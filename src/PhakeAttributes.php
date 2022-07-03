<?php

namespace Gyro\PhakeAttributes;

trait PhakeAttributes
{
    private array $knownPhakeMockedProperties = [];

    /**
     * @before
     */
    public function initializePhakeMocks() : void
    {
        $reflection = new \ReflectionObject($this);

        foreach ($reflection->getProperties() as $reflectionProperty) {
            $mockAttributes = $reflectionProperty->getAttributes(Mock::class);

            if (count($mockAttributes) > 0) {
                $type = $reflectionProperty->getType();

                if ($type instanceof \ReflectionNamedType) {
                    $mock = \Phake::mock($type->getName());
                    $this->knownPhakeMockedProperties[$reflectionProperty->getName()] = $mock;

                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($this, $mock);
                } else if ($type instanceof \ReflectionIntersectionType || $type instanceof \ReflectionUnionType) {
                    $interfaces = [];
                    foreach ($type->getTypes() as $intersectionType) {
                        if ($intersectionType->getName() !== \Phake\IMock::class) {
                            $interfaces[] = $intersectionType->getName();
                        }
                    }
                    $mock = \Phake::mock($interfaces);
                    $this->knownPhakeMockedProperties[$reflectionProperty->getName()] = $mock;

                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($this, $mock);
                } else {
                    throw new \RuntimeException(sprintf(
                        'Cannot #[Mock] property %s with no or wrong types.',
                        $reflectionProperty->getName()
                    ));
                }
            }
        }
    }

    /**
     * @template T
     * @psalm-param class-string<T> $className
     * @return T
     */
    protected function newInstanceWithMockedArgumentsFor(string $className) : object
    {
        return new $className(...$this->mockArgumentsFor($className));
    }

    protected function mockArgumentsFor(string $className) : array
    {
        $reflectionClass = new \ReflectionClass($className);
        $ctor = $reflectionClass->getConstructor();

        $arguments = [];
        foreach ($ctor->getParameters() as $reflectionParameter) {
            $paramName = $reflectionParameter->getName();

            if ($reflectionParameter->hasType()) {
                foreach ($this->knownPhakeMockedProperties as $name => $mock) {
                    if ($reflectionParameter->getType()->getName() === get_parent_class($mock) ||
                        in_array($reflectionParameter->getType()->getName(), class_implements($mock))) {
                        $arguments[$paramName] = $mock;
                        break;
                    }
                }
            } else if ($this->knownPhakeMockedProperties[$paramName]) {
                $arguments[$paramName] = $this->knownPhakeMockedProperties[$paramName];
            }
        }

        return $arguments;
    }
}