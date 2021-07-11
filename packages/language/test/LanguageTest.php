<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Language\Test;

use Windwalker\Language\Language;

/**
 * Test class of Language
 *
 * @since 2.0
 */
class LanguageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var Language
     */
    protected Language $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new Language();

        $this->instance->load(__DIR__ . '/fixtures/ini/en-GB.ini', 'ini', 'en-GB')
            ->load(__DIR__ . '/fixtures/ini/zh-TW.ini', 'ini', 'zh-TW');
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
     * Method to test load().
     *
     * @return void
     */
    public function testLoad()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test trans().
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testTrans()
    {
        $this->instance->setLocale('zh-TW');

        self::assertEquals('花', $this->instance->trans('WINDWALKER_LANGUAGE_TEST_FLOWER'));
        self::assertEquals('Olive', $this->instance->trans('WINDWALKER_LANGUAGE_TEST_Olive'));
        self::assertEquals('Sunflower', $this->instance->trans('Windwalker Language Test Sunflower'));

        self::assertEquals('A key not exists', $this->instance->trans('A key not exists'));

        $this->instance->setDebug(true);

        self::assertEquals('**Sunflower**', $this->instance->trans('Windwalker Language Test Sunflower'));
        self::assertEquals('??A key not exists??', $this->instance->trans('A key not exists'));
    }

    /**
     * Method to test choice().
     *
     * @return void
     *
     * @throws \ReflectionException
     * @covers \Windwalker\Language\Language::choice
     */
    public function testChoice()
    {
        self::assertEquals('No Sunflower', $this->instance->choice('Windwalker Language Test Sunflower Choice', 0));
        self::assertEquals('Sunflower', $this->instance->choice('Windwalker Language Test Sunflower Choice', 1));
        self::assertEquals('Sunflowers', $this->instance->choice('Windwalker Language Test Sunflower Choice', 2));

        $this->instance->setLocale('zh-TW');

        self::assertEquals('沒有花', $this->instance->choice('Windwalker Language Test flower Choice', 0));
        self::assertEquals('花', $this->instance->choice('Windwalker Language Test flower Choice', 1));
        self::assertEquals('花', $this->instance->choice('Windwalker Language Test flower Choice', 2));
    }

    /**
     * Method to test sprintf().
     *
     * @return void
     */
    public function testReplace()
    {
        self::assertEquals(
            'The Sakura is beautiful~~~!!!',
            $this->instance->trans('WINDWALKER_LANGUAGE_TEST_BEAUTIFUL_FLOWER', 'Sakura')
        );
        self::assertEquals(
            'The Sunflower is beautiful~~~!!!',
            $this->instance->trans('WINDWALKER_LANGUAGE_TEST_BEAUTIFUL_FLOWER', 'Sunflower')
        );
    }

    /**
     * Method to test exists().
     *
     * @return void
     *
     * @covers \Windwalker\Language\Language::exists
     */
    public function testHas()
    {
        self::assertTrue($this->instance->has('Windwalker Language Test flower'));
        self::assertFalse($this->instance->has('Windwalker Language Test rose'));
    }

    /**
     * Method to test addString().
     *
     * @return void
     *
     * @throws \ReflectionException
     * @covers \Windwalker\Language\Language::addString
     */
    public function testAddString()
    {
        $this->instance->addString('windwalker language test rose', 'Rose');

        self::assertTrue($this->instance->has('Windwalker Language Test rose'));

        self::assertEquals('Rose', $this->instance->trans('WINDWALKER_LANGUAGE_TEST_ROSE'));
    }

    /**
     * Method to test addStrings().
     *
     * @return void
     *
     * @throws \ReflectionException
     * @covers \Windwalker\Language\Language::addStrings
     * @TODO   Implement testAddStrings().
     */
    public function testAddStrings()
    {
        $strings = [
            'foo' => 'bar',
            'wind' => 'walker',
        ];

        $this->instance->addStrings($strings);

        self::assertEquals('bar', $this->instance->trans('foo'));
        self::assertEquals('walker', $this->instance->trans('wind'));
    }

    public function testParent()
    {
        $this->instance->setLocale('zh-TW');
        $child = $this->instance->extract('windwalker.language');
        $r = $child->trans('test.flower');

        self::assertEquals(
            '花',
            $r
        );
    }

    /**
     * Method to test setDebug().
     *
     * @return void
     *
     * @throws \ReflectionException
     * @covers \Windwalker\Language\Language::setDebug
     */
    public function testSetDebug()
    {
        $this->instance->setDebug(true);

        self::assertEquals('**Sunflower**', $this->instance->trans('Windwalker Language Test Sunflower'));
        self::assertEquals('??A key not exists??', $this->instance->trans('A key not exists'));
    }

    /**
     * Method to test getOrphans().
     *
     * @return void
     *
     * @throws \ReflectionException
     * @covers \Windwalker\Language\Language::getOrphans
     */
    public function testGetOrphans()
    {
        $this->instance->setDebug(true);

        // Exists
        $this->instance->trans('Windwalker Language Test Sakura');

        // Not exists
        $this->instance->trans('Windwalker Language Test No exists flower');
        $this->instance->trans('A key not exists');
        $line = __LINE__ - 1;

        $orphans = $this->instance->getOrphans();

        self::assertEquals(['windwalker.language.test.no.exists.flower', 'a.key.not.exists'], array_keys($orphans));

        $caller = $orphans['a.key.not.exists']['caller'];

        $ref = new \ReflectionMethod($this, __FUNCTION__);
        self::assertEquals(__METHOD__, $caller['class'] . '::' . $caller['function']);
        self::assertEquals(__FILE__, $caller['file']);
        self::assertEquals($line, $caller['line']);

        $called = $orphans['a.key.not.exists']['called'];
        self::assertEquals('Windwalker\Language\Language::trans', $called['class'] . '::' . $called['function']);
    }

    /**
     * Method to test getUsed().
     *
     * @return void
     *
     * @throws \ReflectionException
     * @covers \Windwalker\Language\Language::getUsed
     */
    public function testGetUsed()
    {
        // Exists
        $this->instance->trans('Windwalker Language Test Sakura');

        // Not exists
        $this->instance->trans('Windwalker Language Test No exists flower');
        $this->instance->trans('A key not exists');

        $used = $this->instance->getUsed();

        self::assertEquals(['windwalker.language.test.sakura'], $used);
    }

    /**
     * Method to test getLocale().
     *
     * @return void
     *
     * @covers \Windwalker\Language\Language::getLocale
     */
    public function testGetAndSetLocale()
    {
        self::assertEquals('en-GB', $this->instance->getLocale());

        $this->instance->setLocale('zh_tw');

        self::assertEquals('zh-TW', $this->instance->getLocale());
    }

    /**
     * Method to test normalize().
     *
     * @return void
     *
     * @covers \Windwalker\Language\Language::normalize
     */
    public function testNormalize()
    {
        self::assertEquals('windwalker.is.good', $this->instance->normalize('Windwalker is good ~~~!!!'));

        $this->instance->setNormalizeHandler(
            function ($value) {
                return 'WINDWALKER-ROCKS';
            }
        );

        self::assertEquals('WINDWALKER-ROCKS', $this->instance->normalize('Windwalker is good ~~~!!!'));
    }
}
