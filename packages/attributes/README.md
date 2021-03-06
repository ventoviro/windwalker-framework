# Windwalker Attributes Resolver Package

This package provides a universal interface to manage [PHP8 Attributes](https://stitcher.io/blog/attributes-in-php-8) ([RFC](https://wiki.php.net/rfc/attributes_v2)) 
and help developers construct the attribute processors.

## Table of Contents

* [Installation](#installation)
* [Getting Started](#getting-started)
* [Available Types & Actions](#available-types---actions)
    + [Object & Classes](#object---classes)
    + [Function & Method](#function---method)
    + [Callable](#callable)
    + [Properties](#properties)
    + [Parameters](#parameters)
* [Write Your Own Attribute Handlers](#write-your-own-attribute-handlers)
    + [Object & Classes](#object---classes-1)
    + [Use Custom Object Builder](#use-custom-object-builder)
    + [Functions & Methods](#functions---methods)
    + [Callable](#callable-1)
    + [Parameters](#parameters-1)
    + [Properties](#properties-1)
* [About `AttributeHandler`](#about--attributehandler-)
* [Integrate to Any Objects](#integrate-to-any-objects)
* [Run if Attributes Exists](#run-if-attributes-exists)
* [Available Handling Methods](#available-handling-methods)

## Installation

This package is currently in Alpha, you must allow dev version in your composer settings.

```
composer require windwaker/attributes dev-master
``` 

## Getting Started

First, you must create your own Attributes. This is a simple example wrapper to wrap any object.

```php
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Wrapper implements AttributeInterface
{
    public object $inner;

    public function __invoke(AttributeHandler $handler): callable
    {
        return function () use ($handler) {
            $this->inner = $handler();
            return $this;
        };
    }
}
```

In `__invoke()`, always return a callback, you can do what you want in this callback.

The `$handler()` will return the value which return by previous attribute handler. 
All callbacks will be added to a stack and run after all attributes processed. This is very similar 
to middleware handler.

Then, register this attribute to resolver.

```php
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;

$attributes = new AttributesResolver();
$attributes->registerAttribute(\Wrapper::class, AttributeType::CLASSES);

// Now, try to wrap an object.
            
#[\Wrapper] 
class Foo {
    
}

$foo = new \Foo();
$foo = $attributes->decorateObject($foo);

$foo instanceof \Wrapper;
$foo->inner instanceof \Foo;
```

## Available Types & Actions

Currently, there has 7 types, You can use `registerAttribute()` to control attribute working scope.

- `CLASSES`: Same with `Attribute::TARGET_CLASS`
- `CLASS_CONSTANTS`: Same with `Attribute::TARGET_CLASS_CONSTANT`
- `METHODS`: Same with `Attribute::TARGET_METHOD`
- `FUNCTIONS`: Same with `Attribute::TARGET_FUNCTION`
- `CALLABLE`: **Special type only provided by `AttributeType`.**
- `PROPERTIES`: Same with `Attribute::TARGET_PROPERTY`
- `PARAMETERS`: Same with `Attribute::TARGET_PARAMETER`

### Object & Classes 

```php
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;

$attributes = new AttributesResolver();

// Work on Class and Object
$attributes->registerAttribute(\Decorator::class, AttributeType::CLASSES);

// Decorate existing object
$object = $attributes->decorateObject($object);

// Create object from class and decorate it.
$object = $attributes->createObject(\Foo::class, ...$args);
```

### Function & Method

```php
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;

$attributes = new AttributesResolver();

// Work on method and function.
$attributes->registerAttribute(\AOP::class, AttributeType::METHODS | AttributeType::FUNCTIONS);

$object = $attributes->resolveMethods(new SomObject());
```

### Callable

Callable type is a special type, allows `AttributesResolver` to call any callable and
wrap the calling process. You can replace parameters or change the return value.

This type works on methods, functions, closures and any callable.

```php
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;

$attributes = new AttributesResolver();

// Work on method, function, Closure or callable.
$attributes->registerAttribute(\Autowire::class, AttributeType::CALLABLE);

$result = $attributes->call($callable, ...$args);
```

### Properties

```php
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;

$attributes = new AttributesResolver();

// Work on object properties
$attributes->registerAttribute(\Inject::class, AttributeType::PROPERTIES);

$object = new class {
    #[\Inject]
    protected ?\Foo $foo = null;
};

$object = $attributes->resolveProperties($object);
```

### Parameters

```php
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;

$attributes = new AttributesResolver();

// Work on callable parameters.
$attributes->registerAttribute(\StrUpper::class, AttributeType::PROPERTIES);
$func = function (
    #[\StrUpper]
    $foo    
) {
    return $foo;
};

$result = $attributes->call($func, ['flower'], /* $context to bind this */); // "FLOWER"
```

## Write Your Own Attribute Handlers

### Object & Classes

This is a Decorator example:  

```php
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;

#[\Attribute(\Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Decorator implements AttributeInterface
{
    protected string $class;
    
    protected array $args = [];
    
    public function __construct(string $class, ...$args)
    {
        $this->class = $class;
        $this->args = $args;
    }

    public function __invoke(AttributeHandler $handler): callable
    {
        return fn (...$newInstanceArgs) => new ($this->class)($handler(...$newInstanceArgs), ...$this->args); 
    }
}
```

There are 2 methods can decorate object or class.

- `decorateObject(object $object): object`
- `createObject(string $class, ...$args): object`

If you call `decorateObject($object)`, the `$handler(<void>)` will only return object which you sent into.

And if you call `createObject($class, ...$args)`, the `$handler(...$args)` will create object 
by the class and pass `...$args` to constructor.

Then, use your own function wrap it, all handlers will be a callback stack and called after all attributes processed.

Example:

```php
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;

#[\Decorator(\Component::class, ['template' => 'foo.php'])]
class Foo 
{
    //
}

$attributes = new AttributesResolver();

// Work on Class and Object
$attributes->registerAttribute(\Decorator::class, AttributeType::CLASSES);

// Decorate existing object
$component = $attributes->decorateObject($object);

// Create object from class and decorate it.
$component = $attributes->createObject(\Foo::class, ...$args);
```

### Use Custom Object Builder

If you want to integrate with some Container packages, please set custom object builder.

```php
$attributes->setBuilder(function (string $class, ...$args) use ($container) {
    return $container->createObject($class, ...$args);
});
```

> TODO: Support custom call() handler.


### Functions & Methods

Functions & Methods type will not return anything, use this type to determine attributes exists and do something else.
This is an example to register methods to another object.

```php
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ListenTo implements AttributeInterface
{
    public function __construct(protected string $event) 
    {
        //
    }

    public function __invoke(AttributeHandler $handler): callable
    {
        return function () use ($handler) {
            $provider = $handler->getResolver()->getOption('provider');

            $listener = $handler();

            $provider->addListener(
                $this->event,
                $listener
            );

            return $listener;
        };
    }
}
```

The `$handler()` will just return method callable array `[$object, 'method_name'']` or function name.

```php
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;

class Subscriber 
{
    #[\ListenTo(\FooEvent::class)]
    public function foo()
    {
        //
    }
}

$attributes = new AttributesResolver();

$attributes->registerAttribute(\ListenTo::class, AttributeType::METHODS | AttributeType::FUNCTIONS);
$attributes->resolveMethods(new \Subscriber());
```

### Callable

An example to control HTTP allow methods and Json Response.

```php
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
class Method implements AttributeInterface
{
    protected array $allows = [];
    
    public function __construct(string|array $allows = [])
    {
        $this->allows = array_map('strtoupper', (array) $allows);
    }

    public function __invoke(AttributeHandler $handler): callable
    {
        return function ($request, $reqHandler) use ($handler) {
            if (!in_array($request->getMethod(), $this->allows, true)) {
                throw new \RuntimeException('Invalid Method', 405);
            }
            // You can change parameters here.
    
            $res = $handler($request, $reqHandler);

            // You can also modify return value.
            return $res;
        }; 
    }
}
```

```php
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
class Json implements AttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable
    {
        return function ($request, $reqHandler) use ($handler) {
            $res = $handler($request, $reqHandler);
            $res = $res->withHeader('Content-Type', 'application/json');
            return $res;
        }; 
    }
}
```

The `$handler(...$args)` in callable attributes is to call the target callable, we can change/validate parameters 
or modify the return value.

Usage:

```php
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;

class Controller 
{
    #[\Method('GET')]
    #[\Json]
    public function index():Response
    {
        return new Response();
    }
}

$attributes = new AttributesResolver();

$attributes->registerAttribute(\Method::class, AttributeType::CALLABLE);
$attributes->registerAttribute(\Json::class, AttributeType::CALLABLE);

// Call
$jsonResponse = $attributes->call(
    [new \Controller(), 'index'], // Callable 
    [$request, 'handler' => $resHandler], // Args should be array, support php8 named arguments
    [?object $context = null] // Context is an object wll bind as this for the callable, default is NULL. 
);
```

### Parameters

An example to handler parameters to upper case

```php
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Upper implements AttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable
    {
        return fn () => strtoupper((string) $handler());
    }
}
```

The `$handler()` in parameter attributes is to simply get parameter values, you can modify this value and return it.

```php
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;

class Http 
{
    public static function request(
        #[\Upper]
        string $method,
        mixed $data = null,
        array $options = []
    ) {
        // $method should always upper case.
    }
}

$attributes = new AttributesResolver();

$attributes->registerAttribute(\Upper::class, \Attribute::TARGET_PARAMETER);

// Decorate existing object
$jsonResponse = $attributes->call([\Http::class, 'request'], ['POST', 'foo=bar']);
```

### Properties

This is an example to handle all properties of an object.

```php
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Wrapper implements AttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable
    {
        /** @var $ref ReflectionProperty */
        $ref = $handler->getReflector();

        // Since php8 supports union type, we should get first exists class type as possible type.
        $type = ReflectionHelper::getFirstExistsClassType($ref);
        $class = $type->getName();

        return fn () => new $class($handler());
    }
}
```

The `$handler()` in properties attributes is to simply get property values, you can modify this value and return it.
No matter these properties are public or protected, AttributesResolver will force set value into it.

Usage:

```php
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;

$object = new class {
    #[\Wrapper]
    protected ?Collection $options = null;
};

$attributes = new AttributesResolver();

$attributes->registerAttribute(\Wrapper::class, \Attribute::TARGET_PROPERTY);

$object = $attributes->resolveProperties($object);
```

## About `AttributeHandler`

`AttributeHandler` is the only parameter of our attribute processor.

```php
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;

#[\Attribute]
class MyAttribute implements AttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable
    {
        /** 
         * $ref can be:
         * @see \ReflectionObject for classes type 
         * @see \ReflectionClass  for classes type
         * @see \ReflectionFunctionAbstract for callable type
         * @see \ReflectionParameter for parameters type
         * @see \ReflectionProperty for properties type
         */
        $ref = $handler->getReflector();
      
        // The AttributesResolver object
        $resolver = $handler->getReflector(); 

        // Get previous result
        $result = $handler(...);
    }
}
```

## Integrate to Any Objects

You can create AttributesResolver in some object to help this object handle attributes, here we use EventDispatcher as example:

```php
use Windwalker\Attributes\AttributesAwareTrait;
use Windwalker\Attributes\AttributesResolver;
use Windwalker\Attributes\AttributeType;

class EventDispatcher 
{
    use AttributesAwareTrait;

    public function __construct()
    {
        $this->prepareAttributes($this->getAttributesResolver());
    }

    protected function prepareAttributes(AttributesResolver $resolver)
    {
        $resolver->registerAttribute(\ListenerTo::class, AttributeType::METHODS);
        $resolver->setOption('dispatcher', $this);
    }
    
    public function addListener(callable $callable)
    {
        // Register listener        
    }
    
    public function subscribe(object $subscriber)
    {
        $this->getAttributesResolver()->resolverMethods($subscriber);        
    }
}
```

Set object to option, then you can access it in attribute handler:

```php
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ListenTo implements AttributeInterface
{
    public function __construct(protected string $event) 
    {
        //
    }

    public function __invoke(AttributeHandler $handler): callable
    {
        return function () use ($handler) {
            $provider = $handler->getResolver()->getOption('dispatcher');

            $listener = $handler();

            $provider->addListener(
                $this->event,
                $listener
            );

            return $listener;
        };
    }
}
```

## Run if Attributes Exists

`AttributesResolver` provides a simple static methods to run any callback if attribute exists.

```php
use Windwalker\Attributes\AttributesAccessor;

$object = new Foo();

AttributesAccessor::runAttributeIfExists(
    new ReflectionObject($object), // Send any reflections
    SomeAttribute::class,
    function (SomeAttribute $attr) {
        // Run anything you want
    }
);

$ref = new ReflectionObject($object);

AttributesAccessor::runAttributeIfExists(
    $ref->getMethod('foo'), // Send ReflectionMethod
    SomeAttribute::class,
    function (SomeAttribute $attr) {
        // Run anything you want
    }
);
```

## Available Handling Methods

| Method | Description |
| --- | --- |
|`createObject(string $class, ...$args): object`| Create object by class and decorate it.|
|`decorateObject(object $object): object`| Decorate an exists object.|
|`call(callable $callable, $args = [], ?object $context = null): mixed`| Call a callable, this will resolve methods, functions and their parameters.|
|`resolveProperties(object $instance): object`| Modify object properties values.|
|`resolveMethods(object $instance): object`| Resolve methods but won't change anything, just call your custom handler.|
|`resolveConstants(object $instance): object`| Resolve class constants but won't change anything, just call your custom handler.|
|`resolveObjectMembers(object $instance): object`| This will run `resolveProperties()`, `resolveConstants()` and `resolveConstants()` one time.|
