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

/**
 * The UniqueIterator class.
 */
class UniqueIterator extends \FilterIterator
{
    /**
     * @var array
     */
    protected array $exists = [];

    /**
     * @var int
     */
    protected int $flags = SORT_STRING;

    /**
     * @inheritDoc
     */
    public function __construct(Iterator $iterator, int $flags = SORT_STRING)
    {
        parent::__construct($iterator);

        $this->flags = $flags;
    }

    /**
     * @inheritDoc
     */
    public function accept(): bool
    {
        $current = $this->current();

        $result = !in_array($this->formatValue($current), $this->exists);

        if ($result) {
            $this->exists[] = $current;
        }

        return $result;
    }

    /**
     * formatValue
     *
     * @param  mixed  $value
     *
     * @return  mixed
     */
    protected function formatValue($value): mixed
    {
        switch ($this->flags) {
            case SORT_NUMERIC:
                return (float) $value;

            case SORT_STRING:
            case SORT_LOCALE_STRING:
                return (string) $value;

            case SORT_REGULAR:
            default:
                return $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->exists = [];

        parent::rewind();
    }

    /**
     * Method to set property strict
     *
     * @param  int  $flags
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setFlags(int $flags): static
    {
        $this->flags = $flags;

        return $this;
    }
}
