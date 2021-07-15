<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Test;

use Attribute;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use SplPriorityQueue;
use SplQueue;
use SplStack;
use Windwalker\Data\Collection;
use Windwalker\DI\Attributes\AttributeType;
use Windwalker\DI\Attributes\Inject;
use Windwalker\DI\Container;
use Windwalker\DI\Exception\DefinitionException;
use Windwalker\DI\Exception\DefinitionResolveException;
use Windwalker\DI\Exception\DependencyResolutionException;
use Windwalker\DI\Test\Injection\HelloInner;
use Windwalker\DI\Test\Injection\HelloWrapper;
use Windwalker\DI\Test\Injection\StubInject;
use Windwalker\DI\Test\Injection\StubService;
use Windwalker\DI\Test\Mock\Bar;
use Windwalker\DI\Test\Mock\Bar2;
use Windwalker\DI\Test\Mock\Foo;
use Windwalker\DI\Test\Mock\StubStack;
use Windwalker\DI\Test\Mock\UnionTypeStub;
use Windwalker\DI\Test\Mock\WithVariadic;
use Windwalker\DI\Test\Stub\StubServiceProvider;
use Windwalker\Scalars\ArrayObject;
use Windwalker\Scalars\StringObject;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Utilities\Reflection\ReflectAccessor;
use Windwalker\Utilities\TypeCast;

use function Windwalker\DI\create;
use function Windwalker\str;

/**
 * The ContainerTest class.
 */
class ContainerTest extends TestCase
{
    use BaseAssertionTrait;

    protected ?Container $instance;

    public function testGetAndSet()
    {
        $container = new Container();

        // Not share, not protect
        $container->set(
            'flower',
            fn() => new \ArrayObject()
        );

        self::assertInstanceOf(\ArrayObject::class, $container->get('flower'));

        self::assertNotSame($container->get('flower'), $container->get('flower'));

        // Share, not protect
        $container->set(
            'sakura',
            fn() => new SplPriorityQueue(),
            Container::SHARED
        );

        self::assertInstanceOf(SplPriorityQueue::class, $container->get('sakura'));

        self::assertSame($container->get('sakura'), $container->get('sakura'));

        // Override it
        $container->set(
            'sakura',
            fn() => new SplStack(),
            Container::SHARED
        );

        // Should be override
        self::assertInstanceOf(SplStack::class, $container->get('sakura'));
    }

    public function testSetAsProtect()
    {
        $this->expectException(DefinitionException::class);

        $container = new Container();

        // Share, Protect
        $container->set(
            'olive',
            fn() => new SplStack(),
            Container::SHARED | Container::PROTECTED
        );

        self::assertInstanceOf(SplStack::class, $container->get('olive'));

        $container->set(
            'olive',
            fn() => new SplQueue(),
            Container::SHARED
        );

        // Should not be override
        self::assertInstanceOf(SplStack::class, $container->get('olive'));
    }

    public function testSetAndRemoveAlias()
    {
        $container = new Container();

        $container->share('Flower', 'Sakura')->alias('FlowerAlias', 'Flower');

        $container->share('FlowerAlias', 'Olive');

        self::assertEquals('Olive', $container->get('FlowerAlias'));
    }

    /**
     * testGetFromParent
     *
     * @return  void
     */
    public function testGetFromParent()
    {
        $container = new Container($this->instance);

        self::assertEquals('World', $container->get('Hello'));
    }

    /**
     * Method to test protect().
     *
     * @return void
     *
     * @covers \Windwalker\DI\Container::protect
     */
    public function testProtect()
    {
        $this->expectException(DefinitionException::class);

        $container = new Container();

        // Share, Protect
        $container->protect(
            'olive',
            fn() => new SplStack(),
            Container::SHARED
        );

        self::assertInstanceOf(SplStack::class, $container->get('olive'));

        $container->set(
            'olive',
            fn() => new SplQueue(),
            Container::SHARED
        );

        // Should not be override
        self::assertInstanceOf(SplStack::class, $container->get('olive'));
    }

    /**
     * Method to test share().
     *
     * @return void
     *
     * @covers \Windwalker\DI\Container::share
     */
    public function testShare()
    {
        $container = new Container();

        // Share, not protect
        $container->share(
            'sakura',
            fn() => new SplPriorityQueue()
        );

        self::assertInstanceOf(SplPriorityQueue::class, $container->get('sakura'));

        self::assertSame($container->get('sakura'), $container->get('sakura'));

        // Override it
        $container->set(
            'sakura',
            fn() => new SplStack(),
            Container::SHARED
        );

        // Should be override
        self::assertInstanceOf(SplStack::class, $container->get('sakura'));
    }

    /**
     * Method to test alias().
     *
     * @return void
     *
     * @covers \Windwalker\DI\Container::alias
     */
    public function testAlias()
    {
        $this->instance->alias('foo', 'flower');

        self::assertEquals('sakura', $this->instance->get('foo'));
    }

    /**
     * Method to test exists().
     *
     * @return void
     */
    public function testHas()
    {
        self::assertTrue($this->instance->has('Hello'));
        self::assertFalse($this->instance->has('Wind'));
    }

    /**
     * Method to test getNewInstance().
     *
     * @return void
     */
    public function testGetNewInstance()
    {
        $container = new Container();

        $container->share(
            'flower',
            fn() => new \ArrayObject()
        );

        self::assertInstanceOf('ArrayObject', $container->get('flower'));

        self::assertNotSame($container->get('flower'), $container->get('flower', true));
    }

    /**
     * Method to test createObject().
     *
     * @return void
     *
     * @covers \Windwalker\DI\Container::createObject
     * @covers \Windwalker\DI\Container::createSharedObject
     * @covers \Windwalker\DI\Container::newInstance
     * @throws DefinitionException
     */
    public function testCreateObject()
    {
        $container = new Container(null, Container::AUTO_WIRE);

        $foo = $container->createObject(Foo::class);

        self::assertInstanceOf(Foo::class, $foo);
        self::assertInstanceOf(Bar::class, $foo->bar);
        self::assertInstanceOf(SplPriorityQueue::class, $foo->bar->queue);
        self::assertInstanceOf(SplStack::class, $foo->bar->stack);

        // Bind a sub class
        $container->clear();

        $container->share('SplStack', new StubStack());

        $foo = $container->createObject(Foo::class);

        self::assertInstanceOf(StubStack::class, $foo->bar->stack);

        // Bind not shared classes
        $container->clear();

        $container->set(
            'SplPriorityQueue',
            fn() => new SplPriorityQueue()
        );

        $queue = $container->get('SplPriorityQueue');

        $foo = $container->createObject(Foo::class);

        self::assertNotSame($queue, $foo->bar->queue, 'Non shared class should be not same.');

        // Auto created classes should be not shared
        $container->clear();

        $bar1 = $container->createObject(Bar::class);
        $bar2 = $container->createObject(Bar2::class);

        self::assertNotSame($bar1->queue, $bar2->queue);

        // Not shared object
        $container->clear();

        $foo = $container->createObject(Foo::class);
        $foo2 = $container->get(Foo::class);

        self::assertNotSame($foo, $foo2);

        // Shared object
        $container->clear();

        $foo = $container->createSharedObject(Foo::class);
        $foo2 = $container->get(Foo::class);

        self::assertSame($foo, $foo2);
    }

    public function testNewInstanceWithCallable()
    {
        $container = new Container(null, Container::AUTO_WIRE);

        $foo = $container->newInstance(fn(Bar $bar) => new Foo($bar));

        self::assertInstanceOf(Bar::class, $foo->bar);
    }

    public function testNewInstanceWithUnionTypes()
    {
        $container = new Container(null, Container::AUTO_WIRE);

        $obj = $container->newInstance(UnionTypeStub::class);

        self::assertInstanceOf(
            ArrayObject::class,
            $obj->iter,
        );
    }

    /**
     * @see  Container::newInstance
     */
    public function testNewInstanceWithoutAutowire(): void
    {
        $container = new Container(null, Container::AUTO_WIRE);

        $container->newInstance(Foo::class, [], 0);

        $container->prepareObject(Bar::class);

        $foo = $container->newInstance(Foo::class, [], 0);

        self::assertInstanceOf(Foo::class, $foo);

        // No autowire on default options
        $container = new Container();

        self::assertExpectedException(
            fn() => $container->newInstance(Foo::class, []),
            DefinitionResolveException::class
        );

        // force autowire at calling
        $foo = $container->newInstance(Foo::class, [], Container::AUTO_WIRE);
    }

    /**
     * Method to test newInstance().
     *
     * @return void
     *
     * @covers \Windwalker\DI\Container::prepareObject
     */
    public function testPrepareObject()
    {
        $container = new Container();

        $container->prepareObject(Foo::class, null, Container::AUTO_WIRE);

        $foo = $container->get(Foo::class);

        self::assertInstanceOf(Foo::class, $foo);
    }

    /**
     * Method to test newInstance().
     *
     * @return void
     *
     * @covers \Windwalker\DI\Container::prepareSharedObject
     */
    public function testPrepareSharedObject()
    {
        $container = new Container();

        $container->prepareSharedObject(Foo::class, null, Container::AUTO_WIRE);

        $foo = $container->get(Foo::class);
        $foo2 = $container->get(Foo::class);

        self::assertSame($foo, $foo2);
    }

    /**
     * Method to test creating a class with arguments which not available for default value.
     *
     * @see  https://github.com/ventoviro/windwalker/issues/318
     *
     * @return  void
     * @throws ReflectionException
     * @throws DependencyResolutionException
     */
    public function testNewInstanceWithNoDefaultValueAvailable()
    {
        $container = new Container();

        $obj = $container->newInstance(Collection::class);

        self::assertInstanceOf(Collection::class, $obj);
    }

    /**
     * testNewInstanceWithPropertyAttributes
     *
     * @return  void
     *
     * @throws ReflectionException
     * @throws DependencyResolutionException
     *
     * @since  3.4.4
     */
    public function testNewInstanceWithPropertyAttributes(): void
    {
        $container = new Container();
        $container->getAttributesResolver()
            ->registerAttribute(Inject::class, Attribute::TARGET_PROPERTY);
        StubService::$counter = 0;

        $container->share(
            'stub',
            function () {
                return new StubService();
            }
        );

        /** @var StubInject $obj */
        $obj = $container->newInstance(StubInject::class);

        self::assertInstanceOf(StubService::class, $obj->foo);
        self::assertInstanceOf(StubService::class, ReflectAccessor::getValue($obj, 'bar'));
        self::assertInstanceOf(StubService::class, $obj->baz);
        self::assertInstanceOf(StubService::class, $obj->yoo);
        self::assertEquals(4, $obj->yoo->getCounter());
    }

    public function testNewInstanceWithVariadic()
    {
        $container = new Container();
        $v = $container->newInstance(
            WithVariadic::class,
            [
                str(),
                'foo' => 'bar',
                Collection::class => \Windwalker\collect()
            ]
        );

        self::assertInstanceOf(StringObject::class, $v->args[0]);
        self::assertEquals('bar', $v->args['foo']);
        self::assertInstanceOf(Collection::class, $v->args[Collection::class]);
    }

    /**
     * Method to test createChild().
     *
     * @return void
     *
     * @covers \Windwalker\DI\Container::createChild
     */
    public function testCreateChild()
    {
        self::assertInstanceOf(Container::class, $this->instance->createChild());
        self::assertNotSame($this->instance, $this->instance->createChild());
        self::assertSame($this->instance, $this->instance->createChild()->getParent());
    }

    /**
     * Method to test extend().
     *
     * @return void
     *
     * @covers \Windwalker\DI\Container::extend
     */
    public function testExtend()
    {
        $this->instance->extend(
            'Hello',
            fn($value, $container) => $value . '~~~!!!'
        );

        self::assertEquals('World~~~!!!', $this->instance->get('Hello'));
    }

    /**
     * testFork
     *
     * @return  void
     *
     * @covers \Windwalker\DI\Container::fork
     */
    public function testFork()
    {
        $hello = $this->instance->fork('Hello', 'Hello2');

        self::assertEquals('World', $hello);
        self::assertEquals('World', $this->instance->get('Hello2'));

        $closure = fn() => uniqid();

        $this->instance->share('uniqid', $closure);

        $uid = $this->instance->get('uniqid');

        self::assertEquals($uid, $this->instance->fork('uniqid', 'uniqid2'));
        self::assertEquals($uid, $this->instance->get('uniqid2'));

        self::assertNotEquals($uid, $uid2 = $this->instance->fork('uniqid', 'uniqid3', true));
        self::assertEquals($uid2, $this->instance->get('uniqid3'));
    }

    /**
     * Method to test registerServiceProvider().
     *
     * @return void
     *
     * @covers \Windwalker\DI\Container::registerServiceProvider
     */
    public function testRegisterServiceProvider()
    {
        $this->instance->registerServiceProvider(new StubServiceProvider());

        self::assertEquals('Bingo', $this->instance->get('bingo'));
    }

    /**
     * testArrayAccess
     *
     * @return  void
     */
    public function testArrayAccess()
    {
        self::assertEquals('World', $this->instance['Hello']);

        $this->instance['your'] = 'welcome';

        self::assertEquals('welcome', $this->instance['your']);
    }

    /**
     * @see  Container::call
     */
    public function testCall(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::call
     */
    public function testCallStaticMagicMethod(): void
    {
        $result = $this->instance->call(
            [
                TypeCast::class,
                'tryInteger',
            ],
            [
                '123',
            ]
        );

        self::assertSame(
            123,
            $result
        );
    }

    public function testCallObjectInvoke(): void
    {
        $object = new class () {
            public function __invoke(StringObject $str)
            {
                return 'fooooo';
            }
        };

        $result = $this->instance->call($object, [], null, Container::AUTO_WIRE);

        self::assertSame(
            'fooooo',
            $result
        );
    }

    /**
     * @see  Container::bind
     */
    public function testBind(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::bindShared
     */
    public function testBindShared(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::createSharedObject
     */
    public function testCreateSharedObject(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::getParameters
     */
    public function testGetParameters(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::setParent
     */
    public function testSetParent(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::count
     */
    public function testCount(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::setParameters
     */
    public function testSetParameters(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::getIterator
     */
    public function testGetIterator(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::removeAlias
     */
    public function testRemoveAlias(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::getParents
     */
    public function testGetParents(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::set
     */
    public function testSet(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::getParent
     */
    public function testGetParent(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::setDefinition
     */
    public function testSetDefinition(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::remove
     */
    public function testRemove(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::resolve
     */
    public function testResolve(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Container::resolve
     */
    public function testResolveWithDecorator(): void
    {
        $this->instance->getAttributesResolver()->registerAttribute(
            HelloWrapper::class,
            AttributeType::ALL
        );

        $this->instance->bind(HelloInner::class, create(HelloInner::class, bar: str('Hello')));

        $hello = $this->instance->resolve(HelloInner::class);

        self::assertEquals(
            'World',
            (string) $hello->foo
        );
    }

    /**
     * @see  Container::whenCreating
     */
    public function testWhenCreating(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        $this->instance = new Container();

        $this->instance->set(
            'Hello',
            fn() => 'World'
        );

        $this->instance->share(
            'flower',
            fn() => 'sakura'
        );

        $this->instance->protect(
            'olive',
            fn() => 'peace'
        );
    }

    protected function tearDown(): void
    {
    }
}
