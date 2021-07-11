<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Crypt;

use InvalidArgumentException;
use SodiumException;

use function chr;
use function mb_substr;
use function sodium_crypto_generichash;
use function str_repeat;

use const SODIUM_CRYPTO_GENERICHASH_BYTES;
use const SODIUM_CRYPTO_GENERICHASH_KEYBYTES;

/**
 * The CryptHelper class.
 *
 * @since  2.0
 */
class CryptHelper
{
    /**
     * repeatToLength
     *
     * @param  string   $string
     * @param  integer  $length
     * @param  bool     $cut
     *
     * @return  string
     */
    public static function repeatToLength(string $string, int $length, bool $cut = false): string
    {
        if (strlen($string) >= $length) {
            return $string;
        }

        $string = str_repeat($string, (int) ceil($length / strlen($string)));

        if ($cut) {
            $string = substr($string, 0, $length);
        }

        return $string;
    }

    /**
     * Use a derivative of HKDF to derive multiple keys from one.
     * http://tools.ietf.org/html/rfc5869
     *
     * This is a variant from hash_hkdf() and instead uses BLAKE2b provided by
     * libsodium.
     *
     * Important: instead of a true HKDF (from HMAC) construct, this uses the
     * crypto_generichash() key parameter. This is *probably* okay.
     *
     * @note This method is port of Halite.
     *
     * @param  string  $ikm     Initial Keying Material
     * @param  int     $length  How many bytes?
     * @param  string  $info    What sort of key are we deriving?
     * @param  string  $salt
     *
     * @return string
     *
     * @throws SodiumException
     */
    public static function hkdfBlake2b(
        string $ikm,
        int $length,
        string $info = '',
        string $salt = ''
    ): string {
        // Sanity-check the desired output length.
        if ($length < 0 || $length > (255 * SODIUM_CRYPTO_GENERICHASH_KEYBYTES)) {
            throw new InvalidArgumentException(
                'Argument 2: Bad HKDF Digest Length'
            );
        }

        // "If [salt] not provided, is set to a string of HashLen zeroes."
        if (empty($salt)) {
            $salt = str_repeat("\x00", SODIUM_CRYPTO_GENERICHASH_KEYBYTES);
        }

        // HKDF-Extract:
        // PRK = HMAC-Hash(salt, IKM)
        // The salt is the HMAC key.
        $prk = sodium_crypto_generichash($ikm, $salt);

        // @note $prk should less than SODIUM_CRYPTO_GENERICHASH_KEYBYTES.

        // HKDF-Expand:
        // T(0) = ''
        $t = '';
        $last_block = '';

        for ($block_index = 1; static::strlen($t) < $length; ++$block_index) {
            // T(i) = HMAC-Hash(PRK, T(i-1) | info | 0x??)
            $last_block = sodium_crypto_generichash(
                $last_block . $info . chr($block_index),
                $prk,
                SODIUM_CRYPTO_GENERICHASH_BYTES
            );
            // T = T(1) | T(2) | T(3) | ... | T(N)
            $t .= $last_block;
        }

        // ORM = first L octets of T
        /** @var string $orm */
        $orm = static::substr($t, 0, $length);

        return $orm;
    }

    /**
     * mb safe string length calculator
     *
     * @param  string  $binaryString  The binary string return from crypt().
     *
     * @return  integer  String length.
     *
     * @since   2.0.4
     */
    public static function strlen(string $binaryString): int
    {
        return mb_strlen($binaryString, '8bit');
    }

    /**
     * substr
     *
     * @param  string  $str
     * @param  int     $start
     * @param  null    $length
     *
     * @return  string
     */
    public static function substr(
        string $str,
        int $start = 0,
        $length = null
    ): string {
        if ($length === 0) {
            return '';
        }

        return mb_substr($str, $start, $length, '8bit');
    }

    /**
     * PHP 7 uses interned strings. We don't want altering this one to alter
     * the original string.
     *
     * @param  string  $string
     *
     * @return  string
     */
    public static function strcpy(string $string): string
    {
        $len = mb_strlen($string);

        $new = '';
        $chunk = max($len >> 1, 1);

        for ($i = 0; $i < $len; $i += $chunk) {
            $new .= mb_substr($string, $i, $chunk);
        }

        return $new;
    }
}
