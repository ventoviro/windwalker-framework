<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Test\Bridge;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Windwalker\Session\Bridge\PhpBridge;
use Windwalker\Session\Handler\FilesystemHandler;
use Windwalker\Session\Handler\HandlerInterface;
use Windwalker\Session\Test\SessionVfsTestTrait;

/**
 * The PhpBridgeTest class.
 */
class PhpBridgeTest extends TestCase
{
    use SessionVfsTestTrait;
    use MockeryPHPUnitIntegration;

    protected ?PhpBridge $instance;

    /**
     * @see  PhpBridge::start
     */
    public function testStart(): void
    {
        self::assertEquals(
            PHP_SESSION_NONE,
            $this->instance->getStatus()
        );

        $this->instance->setId(static::$sess1);

        $this->instance->start();

        self::assertEquals(
            PHP_SESSION_ACTIVE,
            $this->instance->getStatus()
        );

        $data = &$this->instance->getStorage();
        $data['flower'] = 'Sakura';

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
            ],
            $data
        );

        $this->instance->writeClose();

        self::assertEquals(
            PHP_SESSION_NONE,
            $this->instance->getStatus()
        );

        self::assertEquals(
            [],
            $data
        );

        $this->instance->setId(static::$sess2);

        $this->instance->start();

        self::assertEquals(
            [
                'flower' => 'Rose',
                'tree' => 'Oak',
            ],
            $data
        );

        $data['animal'] = 'Bird';

        $this->instance->writeClose();

        self::assertEquals(
            'a:3:{s:6:"flower";s:4:"Rose";s:4:"tree";s:3:"Oak";s:6:"animal";s:4:"Bird";}',
            file_get_contents(self::getSessionPath() . '/' . 'sess_' . static::$sess2),
        );
    }

    public function testStartWithNewId(): void
    {
        $this->instance->start();

        $data = &$this->instance->getStorage();

        self::assertEquals(
            [],
            $data
        );

        $data['foo'] = 'Bar';

        $id = $this->instance->getId();

        $this->instance->writeClose();

        self::assertEquals(
            'a:1:{s:3:"foo";s:3:"Bar";}',
            file_get_contents('vfs://root/tmp/sess_' . $id)
        );
    }

    public function testStartWithSuperGlobal(): void
    {
        $this->createInstance(
            [
                PhpBridge::OPTION_WITH_SUPER_GLOBAL => true,
            ]
        );

        $this->instance->setId(static::$sess1);
        $this->instance->start();

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
            ],
            $_SESSION
        );

        $_SESSION['hello'] = 'World';

        self::assertEquals(
            'World',
            $this->instance->getStorage()['hello']
        );
    }

    /**
     * @see  PhpBridge::setSessionName
     */
    public function testSetSessionName(): void
    {
        $this->instance->setSessionName('WW_SESS');

        $handler = Mockery::mock(HandlerInterface::class);
        $handler->shouldReceive('open')
            ->withArgs(fn($savePath, $name) => $name === 'WW_SESS');
        $handler->shouldReceive('write')
            ->andReturnTrue();
        $handler->shouldIgnoreMissing();

        $this->instance->setHandler($handler);

        $this->instance->start();

        $this->instance->writeClose();
    }

    public function testStrictMode()
    {
        $this->createInstance(
            [
                'use_strict_mode' => true,
            ]
        );

        $this->instance->setId(static::$sess1);

        unlink('vfs://root/tmp/sess_' . static::$sess1);

        $this->instance->start();

        self::assertNotEquals(
            static::$sess1,
            $this->instance->getId()
        );
    }

    /**
     * @see  PhpBridge::getStatus
     */
    public function testGetStatus(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  PhpBridge::gcEnabled
     */
    public function testGcEnabled(): void
    {
        $this->createInstance(
            [
                'gc_probability' => 0,
                'gc_divisor' => 1000,
            ]
        );

        self::assertFalse($this->instance->gcEnabled());

        $this->instance->setOption('gc_probability', 1000);

        self::assertTrue($this->instance->gcEnabled());
    }

    /**
     * @see  PhpBridge::getId
     */
    public function testGetId(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  PhpBridge::__construct
     */
    public function test__construct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  PhpBridge::unset
     */
    public function testUnset(): void
    {
        $this->instance->setId(static::$sess1);
        $this->instance->start();

        $data = &$this->instance->getStorage();

        $this->instance->unset();

        self::assertEquals(
            [],
            $data
        );
    }

    /**
     * @see  PhpBridge::setId
     */
    public function testSetId(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  PhpBridge::isStarted
     */
    public function testIsStarted(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  PhpBridge::getStorage
     */
    public function testGetStorage(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  PhpBridge::writeClose
     */
    public function testWriteClose(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  PhpBridge::regenerate
     */
    public function testRegenerate(): void
    {
        $this->instance->setId(static::$sess1);

        $this->instance->start();

        $data = &$this->instance->getStorage();

        $data['tree'] = 'Palm';

        $this->instance->regenerate();

        $newId = $this->instance->getId();

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
                'tree' => 'Palm',
            ],
            $data
        );

        $this->instance->writeClose();

        self::assertNotEquals(
            $newId,
            static::$sess1
        );

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
                'tree' => 'Palm',
            ],
            unserialize(file_get_contents('vfs://root/tmp/sess_' . static::$sess1))
        );
    }

    /**
     * @see  PhpBridge::regenerate
     */
    public function testRegenerateDeleteOld(): void
    {
        $this->instance->setId(static::$sess1);

        $this->instance->start();

        $data = &$this->instance->getStorage();

        $data['tree'] = 'Palm';

        $this->instance->regenerate(true);

        $newId = $this->instance->getId();

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
                'tree' => 'Palm',
            ],
            $data
        );

        $this->instance->writeClose();

        self::assertNotEquals(
            $newId,
            static::$sess1
        );

        self::assertFileDoesNotExist('vfs://root/tmp/sess_' . static::$sess1);
    }

    /**
     * @see  PhpBridge::destroy
     */
    public function testDestroy(): void
    {
        $this->instance->setId(static::$sess1);

        $this->instance->start();

        $data = &$this->instance->getStorage();

        $this->instance->destroy();

        self::assertEquals(
            [],
            $data
        );

        self::assertFileDoesNotExist('vfs://root/tmp/sess_' . static::$sess1);
    }

    /**
     * @see  PhpBridge::getSessionName
     */
    public function testGetSessionName(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        $this->createInstance();
        $this->prepareVfs();
    }

    protected function createInstance(array $options = [])
    {
        $this->instance = new PhpBridge(
            $options,
            new FilesystemHandler(static::getSessionPath())
        );
    }

    protected function resetSessions(): void
    {
        $this->prepareVfs();
    }

    protected function tearDown(): void
    {
        $this->instance->writeClose();
    }
}
