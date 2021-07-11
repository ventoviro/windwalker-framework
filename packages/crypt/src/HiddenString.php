<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Crypt;

use Throwable;

use function sodium_memzero;
use function str_repeat;

/**
 * The HiddenString class.
 */
class HiddenString
{
    /**
     * @var string
     */
    protected $value;

    /**
     * HiddenString constructor.
     *
     * @param  string  $value
     */
    public function __construct(string $value)
    {
        $this->value = CryptHelper::strcpy($value);
    }

    /**
     * Get string back.
     *
     * @return  string
     */
    public function get(): string
    {
        return CryptHelper::strcpy($this->value);
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->get();
    }

    public function __destruct()
    {
        if (function_exists('sodium_memzero')) {
            try {
                sodium_memzero($this->value);

                return;
            } catch (Throwable $e) {
                //
            }
        }

        // If sodium not available, attempt to wipe value from memory.
        $zero = str_repeat("\0", mb_strlen($this->value));

        $this->value ^= ($zero ^ $this->value);

        unset($zero, $this->value);
    }
}
