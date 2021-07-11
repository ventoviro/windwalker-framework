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
use Windwalker\Crypt\Key;
use Windwalker\Crypt\Symmetric\LegacyOpensslCipher;
use Windwalker\Crypt\Symmetric\OpensslCipher;

/**
 * Test class of OpensslCipher
 *
 * @since 2.0
 */
class LegacyOpensslCipherTest extends TestCase
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
     * testLegacy
     *
     * @param  string  $method
     * @param  string  $str
     *
     * @return  void
     *
     * @dataProvider methodsLegacyProvider
     */
    public function testLegacy(string $method, string $str): void
    {
        $key = new Key('foo');

        $cipher = new LegacyOpensslCipher($method);

        $data = $cipher->decrypt($str, $key);

        $this->assertEquals('windwalker', $data->get());
    }

    public function methodsLegacyProvider(): array
    {
        // phpcs:disable
        return [
            'AES-256-CBC' => [
                'AES-256-CBC',
                'EoklxV3fqZO5ma8XwWL7G2cK3i2k5AXKBz9m8PGeE2k=:LkfmL1i7Tjck+mxEnjgGKkb2VPIT8VC2pYV9Sr9BN24=:5CC6bDdRjeyNP+OAYuPolA==:i0i0TSq9oVZfxvcacicj7Q==',
            ],
            'des-ede3-cbc' => [
                'des-ede3-cbc',
                'vH8xcBwXQiXZ/YSvw+h0eWLbnftFHJNb5dc/Ob2vOHU=:MZIUaSKqBsnb0ZeMG5vJDzVwbyrrAPqYoqXNTO6RoUw=:/cEHJARlmjg=:fd0YQLROmEQRiEIyoOcXag==',
            ],
            'bf-cbc' => [
                'bf-cbc',
                '5ZTJ03ITnhshMxghJh/+b9d2+kSAPsGdHrcXXBp7Zso=:MS1jDSc5uxuf30ImrARNdXqn8oFexce+olpGj6PBbpA=:5WjBQfVXLuk=:S54cmXm3Lp3k42q7VRawVQ==',
            ],
        ];
        // phpcs:enable
    }
}
