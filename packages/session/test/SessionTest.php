<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Session\Bridge\BridgeInterface;
use Windwalker\Session\Bridge\NativeBridge;
use Windwalker\Session\Bridge\PhpBridge;
use Windwalker\Session\Cookie\ArrayCookies;
use Windwalker\Session\Cookie\CookiesInterface;
use Windwalker\Session\Handler\ArrayHandler;
use Windwalker\Session\Session;

/**
 * The SessionTest class.
 */
class SessionTest extends TestCase
{
    use SessionVfsTestTrait;

    protected ?Session $instance;

    /**
     * @see  Session::start
     *
     * @session native
     * @handler native
     * @cookie  native
     * @autoCommit true
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testNativeBridgeNativeCookieWithoutId(): void
    {
        $sess = $this->createInstance(
            [],
            new NativeBridge([]),
        );

        // With no name
        $sess->start();

        self::assertEquals(
            [],
            $_SESSION
        );
        self::assertEquals(
            session_id(),
            $sess->getId()
        );
    }

    /**
     * @see  Session::start
     *
     * @session native
     * @handler native
     * @cookie  native
     * @autoCommit true
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testNativeBridgeNativeCookieWithId(): void
    {
        $sess = $this->createInstance(
            [],
            new NativeBridge([]),
        );

        $_COOKIE[$sess->getName()] = static::$sess1;

        // With no name
        $sess->start();

        self::assertEquals(
            [],
            $_SESSION
        );
        self::assertEquals(
            static::$sess1,
            $sess->getId()
        );
    }

    /**
     * @see  Session::start
     *
     * @session native
     * @handler native
     * @cookie  native
     * @autoCommit true
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testNativeBridgeArrayCookieWithId(): void
    {
        $sess = $this->createInstance(
            [
                'ini' => [
                    'save_path' => self::getSessionPath(),
                    'use_cookies' => '0'
                ]
            ],
            new NativeBridge([], new ArrayHandler()),
            ArrayCookies::create(
                [
                    'WW_SESS_ID' => static::$sess1
                ]
            )
        );

        // With name
        $sess->setName('WW_SESS_ID');
        $sess->start();

        self::assertEquals(
            [],
            $_SESSION
        );
        self::assertEquals(
            static::$sess1,
            $sess->getId()
        );
    }

    /**
     * @see  Session::start
     *
     * @session php
     * @handler php
     * @cookie  array
     * @autoCommit true
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testPhpBridgeNativeCookieWithId(): void
    {
        $sess = $this->createInstance(
            [],
            new PhpBridge(
                [],
                $handler = new ArrayHandler(
                    [
                        static::$sess1 => ArrayHandler::createData('a:1:{s:6:"flower";s:6:"Sakura";}')
                    ]
                )
            )
        );

        $sess->setName('FOO_SESS');

        $_COOKIE['FOO_SESS'] = static::$sess1;

        $sess->start();

        self::assertEquals(
            ['flower' => 'Sakura'],
            $sess->all()
        );
        self::assertEquals(
            static::$sess1,
            $sess->getId()
        );

        $sess->set('tree', 'Oak');

        // Write
        $sess->stop(true);

        self::assertEquals(
            'a:3:{s:6:"flower";s:6:"Sakura";s:4:"tree";s:3:"Oak";s:6:"_flash";a:0:{}}',
            $handler->getSessions()[static::$sess1]['data']
        );
    }

    /**
     * @see  Session::start
     *
     * @session php
     * @handler php
     * @cookie  native
     * @autoCommit true
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testPhpBridgeNativeCookieWithoutId(): void
    {
        $sess = $this->createInstance(
            [],
            new PhpBridge(
                [
                    BridgeInterface::OPTION_WITH_SUPER_GLOBAL => true
                ],
                $handler = new ArrayHandler(
                    [
                        static::$sess1 => ArrayHandler::createData('a:1:{s:6:"flower";s:6:"Sakura";}')
                    ]
                )
            )
        );

        $sess->setName('FOO_SESS');

        $sess->start();

        self::assertEquals(
            [],
            $_SESSION
        );
        self::assertNotEquals(
            static::$sess1,
            $sess->getId()
        );
    }

    /**
     * @see  Session::start
     *
     * @session php
     * @handler php
     * @cookie  array
     * @autoCommit true
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testPhpBridgeArrayCookieWithId(): void
    {
        $sess = $this->createInstance(
            [],
            new PhpBridge(
                [
                    BridgeInterface::OPTION_WITH_SUPER_GLOBAL => true
                ],
                new ArrayHandler(
                    [
                        static::$sess1 => ArrayHandler::createData('a:1:{s:6:"flower";s:6:"Sakura";}')
                    ]
                )
            ),
            ArrayCookies::create(
                [
                    'FOO_SESS' => static::$sess1
                ]
            )
        );

        $sess->setName('FOO_SESS');
        $sess->start();

        self::assertEquals(
            ['flower' => 'Sakura'],
            $_SESSION
        );
        self::assertEquals(
            static::$sess1,
            $sess->getId()
        );
    }

    /**
     * @see  Session::addFlash
     */
    public function testAddFlash(): void
    {
        $sess = $this->createInstance(
            [],
            new PhpBridge(
                [],
                new ArrayHandler(
                    [
                        static::$sess1 => ArrayHandler::createData('a:1:{s:6:"flower";s:6:"Sakura";}')
                    ]
                )
            ),
            ArrayCookies::create(
                [
                    'PHPSESSID' => static::$sess1
                ]
            )
        );

        $sess->start();
        $sess->addFlash(
            [
                'Hello',
                'World'
            ],
            'info'
        );

        self::assertEquals(
            [
                'info' => [
                    'Hello',
                    'World'
                ]
            ],
            $sess->getFlashBag()->peek()
        );

        $sess->addFlash('Run', 'danger');

        self::assertEquals(
            [
                'info' => [
                    'Hello',
                    'World'
                ],
                'danger' => [
                    'Run'
                ]
            ],
            $sess->getFlashes()
        );

        self::assertEquals(
            [],
            $sess['_flash']
        );
    }

    /**
     * @see  Session::fork
     */
    public function testFork(): void
    {
        $sess = $this->createInstance(
            [],
            new PhpBridge(
                [],
                new ArrayHandler(
                    [
                        static::$sess1 => ArrayHandler::createData('a:1:{s:6:"flower";s:6:"Sakura";}')
                    ]
                )
            ),
            ArrayCookies::create(
                [
                    'PHPSESSID' => static::$sess1
                ]
            )
        );

        $sess->start();
        $sess2 = $sess->fork();

        self::assertNotSame(
            $sess,
            $sess2
        );

        $sess2['tree'] = 'Oak';

        self::assertNotEquals(
            $sess['tree'] ?? null,
            $sess2['tree']
        );
        self::assertNotEquals(
            $sess->getId(),
            $sess2->getId()
        );

        $sess3 = $sess2->fork(static::$sess2);

        self::assertEquals(
            static::$sess2,
            $sess3->getId()
        );
    }

    /**
     * @see  Session::getCookies
     */
    public function testGetCookies(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::stop
     */
    public function testStop(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::__construct
     */
    public function test__construct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::setCookieParams
     */
    public function testSetCookieParams(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::registerINI
     */
    public function testRegisterINI(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::restart
     */
    public function testRestart(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::getFlashBag
     */
    public function testGetFlashBag(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::setFlashBag
     */
    public function testSetFlashBag(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::jsonSerialize
     */
    public function testJsonSerialize(): void
    {
        $sess = $this->createInstance(
            [],
            new PhpBridge([], new ArrayHandler()),
        );

        $sess['flower'] = 'Sakura';
        $sess['bird'] = 'Eagle';

        self::assertEquals(
            '{"flower":"Sakura","bird":"Eagle"}',
            json_encode($sess, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @see  Session::getName
     */
    public function testGetName(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::clear
     */
    public function testClear(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::setCookies
     */
    public function testSetCookies(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::setName
     */
    public function testSetName(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::all
     */
    public function testAll(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::getStorage
     */
    public function testGetStorage(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::getFlashes
     */
    public function testGetFlashes(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Session::count
     */
    public function testCount(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        $this->instance = null;

        $this->prepareVfs();
    }

    protected function createInstance(array $options = [], ?BridgeInterface $bridge = null, ?CookiesInterface $cookies = null): Session
    {
        return $this->instance = new Session(
            $options,
            $bridge,
            $cookies
        );
    }

    protected function tearDown(): void
    {
    }
}
