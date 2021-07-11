<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Crypt\Test\Symmetric;

use PHPUnit\Framework\TestCase;
use Windwalker\Crypt\HiddenString;
use Windwalker\Crypt\Key;
use Windwalker\Crypt\SafeEncoder;
use Windwalker\Crypt\Symmetric\OpensslCipher;

use function openssl_get_cipher_methods;

/**
 * Test class of OpensslCipher
 *
 * @since 2.0
 */
class OpensslCipherTest extends TestCase
{
    /**
     * Test instance.
     *
     * @var OpensslCipher
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        //
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
     * Method to test encrypt().
     *
     * @param  string  $method
     *
     * @return void
     *
     * @dataProvider methodsProvider
     */
    public function testEncrypt(string $method): void
    {
        $methods = openssl_get_cipher_methods(true);

        if (!in_array(strtolower($method), $methods, true)) {
            self::markTestSkipped('Algorithm: ' . $method . ' no support.');
        }

        $key = new Key('hello');

        $cipher = new OpensslCipher($method);

        $data = $cipher->encrypt(new HiddenString('windwalker'), $key, SafeEncoder::HEX);

        $data = $cipher->decrypt($data, $key, SafeEncoder::HEX);

        $this->assertEquals('windwalker', $data->get());
    }

    public function methodsProvider(): array
    {
        return [
            ['AES-256-CBC'],
            ['AES-128-CFB'],
            ['BF-CBC'],
            ['BF-CFB'],
            ['IDEA-CBC'],
            ['AES128'],
            ['blowfish'],
        ];
    }
}
