<?php

namespace Gyro\PhakeAttributes;

use PHPUnit\Framework\TestCase;

class PhakeAttributeTest extends TestCase
{
    use PhakeAttributes;

    #[Mock]
    public Foo $foo;
    #[Mock]
    public Bar $bar;

    #[Mock]
    public \Countable|\Iterator $unionType;

    public function testMockArgumentsFor() : void
    {
        $args = $this->mockArgumentsFor(Baz::class);

        $baz = new Baz(...$args);

        $this->assertSame($baz->bar, $this->bar);
        $this->assertSame($baz->foo, $this->foo);
    }

    public function testNewInstanceWithMockArgumentsFor() : void
    {
        $baz = $this->newInstanceWithMockedArgumentsFor(Baz::class);

        $this->assertSame($baz->bar, $this->bar);
        $this->assertSame($baz->foo, $this->foo);
    }

    public function testMockMultipleInterfacesUnionTypes() : void
    {
        $this->assertInstanceOf(\Countable::class, $this->unionType);
        $this->assertInstanceOf(\Iterator::class, $this->unionType);
    }
}

class Foo
{
}

class Bar
{

}

class Baz
{
    public function __construct(public Foo $foo, public Bar $bar)
    {
    }
}