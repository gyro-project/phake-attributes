# Phake Attributes

This extension for Phake provides an attribute `#[Mock]` for properties that
can be used in PHPUnit testcases via a trait that adds a before listener:

```php
use Gyro\PhakeAttributes\Mock;
use Gyro\PhakeAttributes\PhakeAttributes;
use PHPUnit\Framework\TestCase;

class MyServiceTestCase extends TestCase
{
    use PhakeAttributes;

    #[Mock] // during setup phase in PHPUnit will call Phake::mock(MyDependencyService::class)
    private MyDependencyService $myDependency;

    public function testMyServiceMethod() : void
    {
        \Phake::when($this->myDependency)->foo()->thenReturn('bar');

        // this uses named arguments and array expansion to pass ctor arguments
        // reflection is used to find the types needed for each argument and the
        // properties and mocks in current test are searched for matches based
        //  on variable name or type.
        $service = new MyService(...$this->mockArgumentsFor(Myservice::class));

        // if you dont want to type that much:
        $service = $this->newInstanceWithMockedArgumentsFor(MyService::class);
    }
}
```
