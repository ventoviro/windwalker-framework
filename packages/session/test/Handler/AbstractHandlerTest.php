<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Test\Handler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Windwalker\Session\Bridge\PhpBridge;
use Windwalker\Session\Cookie\ArrayCookies;
use Windwalker\Session\Handler\AbstractHandler;
use Windwalker\Session\Handler\ArrayHandler;
use Windwalker\Session\Handler\HandlerInterface;
use Windwalker\Session\Session;
use Windwalker\Session\Test\SessionVfsTestTrait;

/**
 * The AbstractHandlerTest class.
 */
abstract class AbstractHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use SessionVfsTestTrait;

    protected ?Session $session;

    /**
     * @var AbstractHandler
     */
    protected $instance;

    /**
     * @see  ArrayHandler::read
     */
    public function testRead(): void
    {
        $session = $this->createSession();

        $session->setId(static::$sess1);
        $session->start();

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
            ],
            $session->all()
        );
    }

    /**
     * @see  ArrayHandler::read
     */
    public function testReadNoStrictWithIdNotExists(): void
    {
        $session = $this->createSession(
            [
                'ini' => [
                    'use_strict_mode' => false,
                ],
            ]
        );

        $session->setId($id = session_create_id());
        $session->start();

        self::assertEquals(
            $id,
            $session->getId()
        );
        self::assertEquals(
            [],
            $session->all()
        );
    }

    /**
     * @see  ArrayHandler::read
     */
    public function testReadStrictWithIdExists(): void
    {
        $session = $this->createSession(
            [
                'use_strict_mode' => true,
            ]
        );

        $session->setId(static::$sess1);
        $session->start();

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
            ],
            $session->all()
        );
    }

    /**
     * @see  ArrayHandler::read
     */
    public function testReadStrictWithIdNotExists(): void
    {
        $session = $this->createSession(
            [
                'use_strict_mode' => true,
            ]
        );

        $session->setId($id = session_create_id());
        $session->start();

        self::assertEquals(
            [],
            $session->all()
        );
        self::assertNotEquals(
            $id,
            $session->getId()
        );
    }

    /**
     * @see  ArrayHandler::updateTimestamp
     */
    abstract public function testUpdateTimestamp(): void;

    /**
     * @see  ArrayHandler::updateTimestamp
     */
    public function testLazyWrite(): void
    {
        $session = $this->createSession();

        /** @var PhpBridge $bridge */
        $bridge = $session->getBridge();

        $mock = Mockery::instanceMock($bridge->getHandler());
        $mock->shouldReceive('updateTimestamp')->andReturnTrue();
        $mock->shouldIgnoreMissing();
        $bridge->setHandler($mock);

        $session->setId(static::$sess1);
        $session->start();
        $session->stop();
    }

    /**
     * @see  ArrayHandler::updateTimestamp
     */
    public function testEagerWrite(): void
    {
        $session = $this->createSession();

        /** @var PhpBridge $bridge */
        $bridge = $session->getBridge();

        $mock = Mockery::instanceMock($bridge->getHandler());
        $mock->shouldReceive('write')->andReturnTrue();
        $mock->shouldIgnoreMissing();
        $bridge->setHandler($mock);

        $session->setId(static::$sess1);
        $session->start();
        $session->stop();
    }

    /**
     * @see  ArrayHandler::write
     */
    public function testWrite(): void
    {
        $session = $this->createSession();

        $session->setId(static::$sess1);
        $session->start();

        $session['animal'] = 'Bear';

        $session->stop();

        self::assertEquals(
            'Bear',
            unserialize($this->instance->read(static::$sess1))['animal']
        );
    }

    /**
     * @see  ArrayHandler::destroy
     */
    public function testDestroy(): void
    {
        $session = $this->createSession();

        $this->instance->destroy(static::$sess1);

        self::assertEquals(
            '',
            $this->instance->read(static::$sess1)
        );
    }

    /**
     * @see  ArrayHandler::gc
     */
    public function testGc(): void
    {
        $session = $this->createSession();

        $this->instance->gc(1440);

        self::assertEquals(
            '',
            $this->instance->read(static::$sess2)
        );
    }

    protected function createSession(array $options = []): Session
    {
        $handler = $this->createInstance();

        return $this->session = new Session(
            [],
            new PhpBridge(
                $options,
                $handler
            ),
            ArrayCookies::create()
        );
    }

    abstract protected function createInstance(): HandlerInterface;

    /**
     * prepareDefaultData
     *
     * @return  array
     */
    protected function prepareDefaultData(): array
    {
        return [
            static::$sess1 => [
                'data' => file_get_contents(self::getSessionPath('sess_' . static::$sess1)),
                'time' => time() - 100,
            ],
            static::$sess2 => [
                'data' => file_get_contents(self::getSessionPath('sess_' . static::$sess2)),
                'time' => time() - 10000,
            ],
        ];
    }

    protected function setUp(): void
    {
        $this->session = null;
    }

    protected function tearDown(): void
    {
    }
}
