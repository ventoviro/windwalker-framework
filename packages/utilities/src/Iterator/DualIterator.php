<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Iterator;

use Iterator;
use RecursiveIterator;

/**
 * The DualIterator class.
 *
 * @note    This is a fork from Marcus Boerger's work:
 *          https://github.com/php/php-src/blob/master/ext/spl/examples/dualiterator.inc
 *
 * @since   2.0
 */
class DualIterator implements Iterator
{
    public const CURRENT_LHS = 0x01;

    public const CURRENT_RHS = 0x02;

    public const CURRENT_ARRAY = 0x03;

    public const CURRENT_0 = 0x00;

    public const KEY_LHS = 0x10;

    public const KEY_RHS = 0x20;

    public const KEY_ARRAY = 0x30;

    public const KEY_0 = 0x00;

    public const DEFAULT_FLAGS = 0x33;

    /**
     * Property lhs.
     *
     * @var  Iterator
     */
    private $lhs;

    /**
     * Property rhs.
     *
     * @var  Iterator
     */
    private $rhs;

    /**
     * Property flags.
     *
     * @var  int
     */
    private $flags;

    /** construct iterator from two iterators
     *
     * @param  Iterator  $lhs    Left  Hand Side Iterator
     * @param  Iterator  $rhs    Right Hand Side Iterator
     * @param  int       $flags  Iteration flags
     */
    public function __construct(Iterator $lhs, Iterator $rhs, $flags = 0x33)
    {
        $this->lhs   = $lhs;
        $this->rhs   = $rhs;
        $this->flags = $flags;
    }

    /**
     * Get lhs.
     *
     * @return Iterator Left Hand Side Iterator
     */
    public function getLHS(): Iterator
    {
        return $this->lhs;
    }

    /**
     * Get rhs.
     *
     * @return Iterator Right Hand Side Iterator
     */
    public function getRHS(): Iterator
    {
        return $this->rhs;
    }

    /**
     * Set flags.
     *
     * @param  int  $flags  new flags
     *
     * @return void
     */
    public function setFlags(int $flags): void
    {
        $this->flags = $flags;
    }

    /**
     * Get flag.
     *
     * @return int Current flags
     */
    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * Rewind both inner iterators
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->lhs->rewind();
        $this->rhs->rewind();
    }

    /**
     * Is valid.
     *
     * @return boolean whether both inner iterators are valid
     */
    public function valid(): bool
    {
        return $this->lhs->valid() && $this->rhs->valid();
    }

    /**
     * Get current item.
     *
     * @return mixed current value depending on CURRENT_* flags
     */
    public function current(): mixed
    {
        switch ($this->flags & 0x0F) {
            default:
            case self::CURRENT_ARRAY:
                return [$this->lhs->current(), $this->rhs->current()];
            case self::CURRENT_LHS:
                return $this->lhs->current();
            case self::CURRENT_RHS:
                return $this->rhs->current();
            case self::CURRENT_0:
                return null;
        }
    }

    /**
     * Get current key.
     *
     * @return mixed current value depending on KEY_* flags
     */
    public function key(): mixed
    {
        switch ($this->flags & 0xF0) {
            default:
            case self::CURRENT_ARRAY:
                return [$this->lhs->key(), $this->rhs->key()];
            case self::CURRENT_LHS:
                return $this->lhs->key();
            case self::CURRENT_RHS:
                return $this->rhs->key();
            case self::CURRENT_0:
                return null;
        }
    }

    /**
     * Move both inner iterators forward
     *
     * @return void
     */
    public function next(): void
    {
        $this->lhs->next();
        $this->rhs->next();
    }

    /**
     * Are Identical.
     *
     * @return boolean Whether both inner iterators are valid and have identical
     *                 current and key values or both are non valid.
     */
    public function areIdentical(): bool
    {
        return $this->valid()
            ? $this->lhs->current() === $this->rhs->current()
            && $this->lhs->key() === $this->rhs->key()
            : $this->lhs->valid() == $this->rhs->valid();
    }

    /**
     * Are equal.
     *
     * @return boolean whether both inner iterators are valid and have equal current
     * and key values or both are non valid.
     */
    public function areEqual(): bool
    {
        return $this->valid()
            ? $this->lhs->current() == $this->rhs->current()
            && $this->lhs->key() == $this->rhs->key()
            : $this->lhs->valid() == $this->rhs->valid();
    }

    /**
     * Compare two iterators.
     *
     * @param  Iterator  $lhs        Left  Hand Side Iterator
     * @param  Iterator  $rhs        Right Hand Side Iterator
     * @param  boolean   $identical  Whether to use areEqual() or areIdentical()
     *
     * @return boolean whether both iterators are equal/identical
     *
     * @note If one implements RecursiveIterator the other must do as well.
     *       And if both do then a recursive comparison is being used.
     */
    public static function compareIterators(Iterator $lhs, Iterator $rhs, bool $identical = false): bool
    {
        if ($lhs instanceof RecursiveIterator) {
            if ($rhs instanceof RecursiveIterator) {
                $it = new RecursiveDualIterator($lhs, $rhs, self::CURRENT_0 | self::KEY_0);
                $it = new RecursiveCompareDualIterator($it);
            } else {
                return false;
            }
        } else {
            $it = new DualIterator($lhs, $rhs, self::CURRENT_0 | self::KEY_0);
        }

        if ($identical) {
            foreach ($it as $n) {
                if (!$it->areIdentical()) {
                    return false;
                }
            }
        } else {
            foreach ($it as $n) {
                if (!$it->areEqual()) {
                    return false;
                }
            }
        }

        return $identical ? $it->areIdentical() : $it->areEqual();
    }
}
