<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Iterator;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Windwalker\Test\Traits\TestAccessorTrait;
use Windwalker\Utilities\Iterator\UniqueIterator;

/**
 * The UniqueIteratorTest class.
 */
class UniqueIteratorTest extends TestCase
{
    use TestAccessorTrait;

    /**
     * @var UniqueIterator
     */
    protected $instance;

    /**
     * @see  UniqueIterator::accept
     */
    public function testAccept(): void
    {
        $iter = new UniqueIterator(new ArrayIterator(['a', 'b', 'a', 'c', 'd', 'd', 'b']));

        self::assertEquals(['a', 'b', 'c', 'd'], array_values(iterator_to_array($iter)));
    }

    /**
     * @see  UniqueIterator::rewind
     */
    public function testRewind(): void
    {
        $iter = new UniqueIterator(new ArrayIterator(['a', 'b', 'a', 'c', 'd', 'd', 'b']));

        foreach ($iter as $item) {
            //
        }

        $iter->rewind();

        self::assertEquals(['a'], $this->getValue($iter, 'exists'));
    }

    protected function setUp(): void
    {
        $this->instance = null;
    }

    protected function tearDown(): void
    {
    }
}
