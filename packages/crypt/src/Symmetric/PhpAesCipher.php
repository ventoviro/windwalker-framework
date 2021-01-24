<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Crypt\Symmetric;

use AesCtr;
use Exception;
use Windwalker\Crypt\HiddenString;
use Windwalker\Crypt\Key;
use Windwalker\Crypt\SafeEncoder;

/**
 * The PhpAesCipher class.
 *
 * @see    https://gist.github.com/chrisns/3992815
 *
 * @since  3.0
 */
class PhpAesCipher implements CipherInterface
{
    public const KEY_128BIT = 128;

    public const KEY_192BIT = 192;

    public const KEY_256BIT = 256;

    /**
     * Property keyLength.
     *
     * @var  int
     */
    protected $keyLength = self::KEY_128BIT;

    /**
     * Method to decrypt a data string.
     *
     * @param  string  $str
     * @param  Key     $key  The private key.
     * @param  string  $encoder
     *
     * @return HiddenString The decrypted data string.
     *
     * @since    2.0
     */
    public function decrypt(string $str, Key $key, string $encoder = SafeEncoder::BASE64URLSAFE): HiddenString
    {
        include_once __DIR__ . '/../lib/aes.class.php';

        return new HiddenString(
            (string) AesCtr::decrypt(base64_decode($str), $key->get(), $this->keyLength)
        );
    }

    /**
     * Method to encrypt a data string.
     *
     * @param  HiddenString  $str
     * @param  Key           $key  The private key.
     * @param  string        $encoder
     *
     * @return  string  The encrypted data string.
     *
     * @since   2.0
     */
    public function encrypt(HiddenString $str, Key $key, string $encoder = SafeEncoder::BASE64URLSAFE): string
    {
        include_once __DIR__ . '/../lib/aes.class.php';

        return base64_encode(AesCtr::encrypt($str->get(), $key->get(), $this->keyLength));
    }

    /**
     * Generate Key.
     *
     * @param  int|null  $length
     *
     * @return  Key
     * @throws Exception
     */
    public static function generateKey(?int $length = 32): Key
    {
        return new Key(random_bytes($length));
    }
}
