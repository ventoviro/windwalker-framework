<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Filter\FilterFactory;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * The FilterFactoryTest class.
 */
class FilterFactoryTest extends TestCase
{
    use BaseAssertionTrait;

    protected ?FilterFactory $instance;

    /**
     * @see  FilterFactory::createFromSyntax
     */
    public function testCreateFromSyntax(): void
    {
        $filter = $this->instance->createFromSyntax('range(min = 5, max=10)');

        self::assertEquals(
            10,
            $filter->filter(20)
        );

        $filter = $this->instance->createFromSyntax('bool');

        self::assertEquals(
            true,
            $filter->filter('Hello')
        );
    }

    public function testCreateMap()
    {
        $map = $this->instance->createNested(
            [
                'id' => 'required|int|range(min=1,max=100)',
                'alias' => 'alnum|length(max=10)|default(hello)',
                'item' => [
                    'content' => 'func(strtoupper)',
                    'params' => fn($v) => json_decode($v, true, 512, JSON_THROW_ON_ERROR),
                ],
            ]
        );

        $result = $map->filter(
            [
                'id' => '600',
                'alias' => 'title is here 123 Hello World',
                'item' => [
                    'intro' => 'Intro',
                    'content' => 'Flower',
                    'params' => '{"foo": "bar"}',
                ],
            ]
        );

        self::assertEquals(
            [
                'id' => 100,
                'alias' => 'titleisher',
                'item' => [
                    'intro' => 'Intro',
                    'content' => 'FLOWER',
                    'params' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
            $result
        );
    }

    public function testValidateMap()
    {
        $this->expectExceptionMessage(
            'Field "id" not match - Validator: Windwalker\Filter\Rule\Range min: 1, max: 100 returns false, value is: string(3) "600"'
        );

        $map = $this->instance->createNested(
            [
                'id' => 'required|int|range(min=1,max=100)',
                'alias' => 'alnum|length(max=10)|default(hello)',
                'item' => [
                    'content' => 'func(strtoupper)',
                    'params' => fn($v) => json_decode($v, true, 512, JSON_THROW_ON_ERROR),
                ],
            ]
        );

        $result = $map->test(
            [
                'id' => '600',
                'alias' => 'title is here 123 Hello World',
                'item' => [
                    'intro' => 'Intro',
                    'content' => 'Flower',
                    'params' => '{"foo": "bar"}',
                ],
            ]
        );
    }

    protected function setUp(): void
    {
        $this->instance = new FilterFactory();
    }

    protected function tearDown(): void
    {
    }
}
