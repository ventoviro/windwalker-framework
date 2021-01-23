<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Crypt\Symmetric;

use Windwalker\Crypt\CryptHelper;
use Windwalker\Crypt\Exception\CryptException;
use Windwalker\Crypt\HiddenString;
use Windwalker\Crypt\Key;
use Windwalker\Crypt\SafeEncoder;

/**
 * The Openssl Cipher class.
 *
 * @note   This cipher class referenced Eugene Fidelin and Peter Mortensen's code to prevent
 *        Chosen-Cipher and Timing attack.
 *
 * @see    http://stackoverflow.com/a/19445173
 *
 * @since  2.0
 *
 * @deprecated  Use SodiumCipher instead.
 */
class LegacyOpensslCipher extends OpensslCipher
{
    /**
     * @inheritDoc
     */
    public function decrypt(string $str, Key $key, string $encoder = SafeEncoder::BASE64): HiddenString
    {
        [$hmac, $salt, $iv, $encrypted] = explode(':', $str);

        $hmac      = SafeEncoder::decode($encoder, $hmac);
        $salt      = SafeEncoder::decode($encoder, $salt);
        $iv        = SafeEncoder::decode($encoder, $iv);
        $encrypted = SafeEncoder::decode($encoder, $encrypted);

        [, $hmacKey] = $this->derivateSecureKeys($key->get(), $salt);

        $calc = $this->hmac($salt . $iv . $encrypted, $hmacKey);

        if (!hash_equals($calc, $hmac)) {
            throw new CryptException('HMAC ERROR: Invalid HMAC.');
        }

        $encKey = CryptHelper::repeatToLength($key->get(), 24, true);

        $decrypted = openssl_decrypt($encrypted, $this->getMethod(), $encKey, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new CryptException('Openssl decrypt failed: ' . openssl_error_string());
        }

        if (function_exists('sodium_memzero')) {
            try {
                \sodium_memzero($calc);
                \sodium_memzero($salt);
                \sodium_memzero($iv);
                \sodium_memzero($hmacKey);
                \sodium_memzero($encrypted);
                \sodium_memzero($encKey);
            } catch (\SodiumException $e) {
                // No actions
            }
        }

        // Decrypt the data.
        return new HiddenString(trim($decrypted));
    }

    /**
     * @inheritDoc
     */
    public function encrypt(HiddenString $str, Key $key, string $encoder = SafeEncoder::BASE64): string
    {
        $salt = OpensslCipher::randomPseudoBytes(static::PBKDF2_SALT_BYTE_SIZE);

        [, $hmacKey] = $this->derivateSecureKeys($key->get(), $salt);

        $iv = $this->getIV();

        $encKey = CryptHelper::repeatToLength($key->get(), 24, true);

        // Encrypt the data.
        $encrypted = openssl_encrypt(
            $str->get(),
            $this->getMethod(),
            $encKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        $hmac = $this->hmac($salt . $iv . $encrypted, $hmacKey);

        $message = implode(
            ':',
            [
                SafeEncoder::encode($encoder, $hmac),
                SafeEncoder::encode($encoder, $salt),
                SafeEncoder::encode($encoder, $iv),
                SafeEncoder::encode($encoder, $encrypted),
            ]
        );

        if (function_exists('sodium_memzero')) {
            try {
                \sodium_memzero($encKey);
                \sodium_memzero($hmacKey);
                \sodium_memzero($iv);
                \sodium_memzero($salt);
                \sodium_memzero($encrypted);
                \sodium_memzero($hmac);
            } catch (\SodiumException $e) {
                // No actions
            }
        }

        return $message;
    }
}
