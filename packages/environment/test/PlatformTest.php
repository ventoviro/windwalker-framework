<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Environment\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Environment\Platform;

/**
 * Test class of Server
 *
 * @since 2.0
 */
class PlatformTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var Platform
     */
    protected $instance;

    /**
     * Property os.
     *
     * @var boolean
     */
    protected $os;

    /**
     * Property isWin.
     *
     * @var  boolean
     */
    protected $isWin;

    /**
     * Property isMac.
     *
     * @var  boolean
     */
    protected $isMac;

    /**
     * Property isUnix.
     *
     * @var  boolean
     */
    protected $isUnix;

    /**
     * Property isLinux.
     *
     * @var  boolean
     */
    protected $isLinux;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new Platform();

        // Detect the native operating system type.
        $this->os = strtoupper(substr(PHP_OS, 0, 3));

        $this->isWin = $this->os === 'WIN';

        $this->isMac = $this->os === 'MAC';

        $this->isUnix = in_array($this->os, ['CYG', 'DAR', 'FRE', 'LIN', 'NET', 'OPE', 'MAC']);

        $this->isLinux = $this->os === 'LIN';
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    /**
     * getOSTestData
     *
     * @return  array
     */
    public function getIsWinTestData(): array
    {
        return [
            ['CYGWIN_NT-5.1', false],
            ['Darwin', false],
            ['FreeBSD', false],
            ['HP-UX', false],
            ['IRIX64', false],
            ['Linux', false],
            ['NetBSD', false],
            ['OpenBSD', false],
            ['SunOS', false],
            ['Unix', false],
            ['WIN32', true],
            ['WINNT', true],
            ['Windows', true],
        ];
    }

    /**
     * getOSTestData
     *
     * @return  array
     */
    public function getIsUnixTestData(): array
    {
        return [
            ['CYGWIN_NT-5.1', true],
            ['Darwin', true],
            ['FreeBSD', true],
            ['HP-UX', true],
            ['IRIX64', true],
            ['Linux', true],
            ['NetBSD', true],
            ['OpenBSD', true],
            ['SunOS', true],
            ['Unix', true],
            ['WIN32', false],
            ['WINNT', false],
            ['Windows', false],
        ];
    }

    /**
     * getOSTestData
     *
     * @return  array
     */
    public function getIsLinuxTestData(): array
    {
        return [
            ['CYGWIN_NT-5.1', false],
            ['Darwin', false],
            ['FreeBSD', false],
            ['HP-UX', false],
            ['IRIX64', false],
            ['Linux', true],
            ['NetBSD', false],
            ['OpenBSD', false],
            ['SunOS', false],
            ['Unix', false],
            ['WIN32', false],
            ['WINNT', false],
            ['Windows', false],
        ];
    }

    /**
     * Method to test getOS().
     *
     * @return void
     *
     * @covers \Windwalker\Environment\Platform::getOS
     */
    public function testGetOS()
    {
        $this->instance->setUname('Darwin');

        $this->assertEquals('DAR', $this->instance->getOS());
    }

    /**
     * Method to test isWin().
     *
     * @param string  $os
     * @param boolean $value
     *
     * @return void
     *
     * @dataProvider getIsWinTestData
     *
     * @covers       Windwalker\Environment\Platform::isWin
     */
    public function testIsWin($os, $value)
    {
        $this->instance->setOS(null);
        $this->instance->setUname($os);

        $this->assertEquals($value, $this->instance->isWindows());
    }

    /**
     * Method to test isUnix().
     *
     * @param string  $os
     * @param boolean $value
     *
     * @return void
     *
     * @dataProvider getIsUnixTestData
     *
     * @covers       \Windwalker\Environment\Platform::isUnix
     */
    public function testIsUnix($os, $value)
    {
        $this->instance->setOS(null);
        $this->instance->setUname($os);

        $this->assertEquals($value, $this->instance->isUnix());
    }

    /**
     * Method to test isLinux().
     *
     * @param string  $os
     * @param boolean $value
     *
     * @return void
     *
     * @dataProvider getIsLinuxTestData
     *
     * @covers       \Windwalker\Environment\Platform::isLinux
     */
    public function testIsLinux($os, $value)
    {
        $this->instance->setOS(null);
        $this->instance->setUname($os);

        $this->assertEquals($value, $this->instance->isLinux());
    }
}
