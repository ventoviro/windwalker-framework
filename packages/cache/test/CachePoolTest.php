<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Test;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Windwalker\Cache\CacheItem;
use Windwalker\Cache\CachePool;
use Windwalker\Cache\Exception\RuntimeException;
use Windwalker\Cache\Serializer\RawSerializer;
use Windwalker\Cache\Storage\ArrayStorage;
use Windwalker\Cache\Storage\StorageInterface;
use Windwalker\Test\Traits\TestAccessorTrait;

/**
 * The CachePoolTest class.
 */
class CachePoolTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use TestAccessorTrait;

    /**
     * @var CachePool
     */
    protected $instance;

    /**
     * testBasicUsage
     *
     * @return  void
     *
     * @throws InvalidArgumentException
     */
    public function testBasicUsage(): void
    {
        $pool = $this->instance;

        $item = $pool->getItem('hello');

        self::assertFalse($item->isHit());

        if (!$item->isHit()) {
            $value = 'Hello World';

            $pool->save($item->set($value));
        }

        self::assertEquals('Hello World', $item->get());
    }

    /**
     * @see  CachePool::setSerializer
     */
    public function testGetSetSerializer(): void
    {
        $this->instance->setSerializer($ser = new RawSerializer());

        self::assertSame($ser, $this->instance->getSerializer());
    }

    /**
     * @see  CachePool::save
     */
    public function testSave(): void
    {
        $item = new CacheItem('foo');
        $item->set('Flower');
        $item->expiresAfter(30);

        $storageMock = Mockery::mock(StorageInterface::class)
            ->shouldReceive('save')
            ->with('foo', 'Flower', time() + 30)
            ->getMock();

        $this->instance->setStorage($storageMock);
        $this->instance->save($item);
    }

    /**
     * @see  CachePool::hasItem
     */
    public function testHasItem(): void
    {
        self::assertFalse($this->instance->hasItem('foo'));

        $this->instance->save((new CacheItem('foo'))->set('Hello'));

        self::assertTrue($this->instance->hasItem('foo'));
    }

    /**
     * @see  CachePool::getItem
     */
    public function testGetItem(): void
    {
        $storageMock = Mockery::mock(StorageInterface::class);
        $storageMock->shouldReceive('get')
            ->with('flower')
            ->andReturn('Sakura');

        $storageMock->shouldReceive('has')
            ->with('flower')
            ->andReturn(true);

        $this->instance->setStorage($storageMock);

        $item = $this->instance->getItem('flower');

        self::assertEquals('Sakura', $item->get());
        self::assertTrue($item->isHit());
    }

    /**
     * @see  CachePool::deleteItem
     */
    public function testDeleteItem(): void
    {
        $storageMock = Mockery::mock(StorageInterface::class);
        $storageMock->shouldReceive('remove')
            ->with('hello')
            ->andReturn(true);

        $this->instance->setStorage($storageMock);

        $this->instance->deleteItem('hello');

        $storageMock = Mockery::mock(StorageInterface::class);
        $storageMock->shouldReceive('remove')
            ->with('hello')
            ->andThrow(RuntimeException::class);

        $this->instance->setStorage($storageMock);

        self::assertFalse($this->instance->deleteItem('hello'));
    }

    /**
     * @see  CachePool::deleteItems
     */
    public function testDeleteItems(): void
    {
        $this->instance->save(new CacheItem('foo', 'FOO'));
        $this->instance->save(new CacheItem('bar', 'BAR'));
        $this->instance->save(new CacheItem('yoo', 'YOO'));

        $this->instance->deleteItems(['foo', 'bar']);

        self::assertFalse($this->instance->getItem('foo')->isHit());
        self::assertFalse($this->instance->getItem('bar')->isHit());
        self::assertTrue($this->instance->getItem('yoo')->isHit());
    }

    /**
     * @see  CachePool::getItems
     */
    public function testGetItems(): void
    {
        $this->instance->save(new CacheItem('foo', 'FOO'));
        $this->instance->save(new CacheItem('bar', 'BAR'));
        $this->instance->save(new CacheItem('yoo', 'YOO'));

        $items = iterator_to_array($this->instance->getItems(['foo', 'yoo']));

        self::assertEquals('FOO', $items['foo']->get());
        self::assertEquals('YOO', $items['yoo']->get());
    }

    /**
     * @see  CachePool::saveDeferred
     */
    public function testSaveDeferred(): void
    {
        $storage = new ArrayStorage();
        $this->instance->setStorage($storage);

        $this->instance->saveDeferred(new CacheItem('foo', 'FOO'));
        $this->instance->saveDeferred(new CacheItem('yoo', 'YOO'));

        self::assertEmpty($storage->getData());

        // Auto commit when destructing
        $this->instance = null;

        self::assertEquals('FOO', $storage->get('foo'));
        self::assertEquals('YOO', $storage->get('yoo'));
    }

    /**
     * @see  CachePool::commit
     */
    public function testCommit(): void
    {
        $storage = new ArrayStorage();
        $this->instance->setStorage($storage);

        $this->instance->saveDeferred(new CacheItem('foo', 'FOO'));
        $this->instance->saveDeferred(new CacheItem('yoo', 'YOO'));

        self::assertEmpty($storage->getData());

        $this->instance->commit();

        self::assertEquals('FOO', $storage->get('foo'));
        self::assertEquals('YOO', $storage->get('yoo'));
    }

    /**
     * @see  CachePool::clear
     */
    public function testClear(): void
    {
        $storage = new ArrayStorage();
        $this->instance->setStorage($storage);

        $this->instance->save(new CacheItem('foo', 'FOO'));
        $this->instance->save(new CacheItem('bar', 'BAR'));
        $this->instance->save(new CacheItem('yoo', 'YOO'));

        $this->instance->clear();

        self::assertEmpty($storage->getData());
    }

    /**
     * @see  CachePool::setStorage
     */
    public function testSetStorage(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::set
     */
    public function testSetGet(): void
    {
        $this->instance->set('hello', 'RRR');

        self::assertEquals('RRR', $this->instance->getStorage()->get('hello'));

        $this->instance->set('hello2', 'RRR2', -5);

        self::assertNull($this->instance->getStorage()->get('hello2'));

        $this->instance->set('hello3', 'RRR3', 10);

        self::assertEquals(
            time() + 10,
            $this->getValue($this->instance->getStorage(), 'data')['hello3'][0]
        );
    }

    /**
     * @see  CachePool::call
     */
    public function testCall(): void
    {
        $i = 0;

        $getter = function () use (&$i) {
            return $this->instance->call(
                'hello',
                static function () use (&$i) {
                    $i++;

                    return 'HELLO-' . $i;
                }
            );
        };

        $getter();
        $getter();
        $r = $getter();

        self::assertEquals('HELLO-' . 1, $r);
    }

    /**
     * @see  CachePool::getMultiple
     */
    public function testGetMultiple(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::deleteMultiple
     */
    public function testDeleteMultiple(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::setMultiple
     */
    public function testSetMultiple(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::delete
     */
    public function testDelete(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::has
     */
    public function testHas(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::__destruct
     */
    public function testDestruct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::getStorage
     */
    public function testGetStorage(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  CachePool::__construct
     */
    public function testConstruct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        $this->instance = new CachePool();
    }

    protected function tearDown(): void
    {
    }
}
