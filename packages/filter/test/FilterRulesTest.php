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
use Windwalker\Filter\FilterInterface;
use Windwalker\Filter\Rule\Absolute;
use Windwalker\Filter\Rule\Alnum;
use Windwalker\Filter\Rule\CastTo;
use Windwalker\Filter\Rule\Cmd;
use Windwalker\Filter\Rule\CompareWith;
use Windwalker\Filter\Rule\EmailAddress;
use Windwalker\Filter\Rule\IPV4;
use Windwalker\Filter\Rule\Length;
use Windwalker\Filter\Rule\Negative;
use Windwalker\Filter\Rule\Range;
use Windwalker\Filter\Rule\UrlAddress;
use Windwalker\Filter\Rule\Words;
use Windwalker\Filter\ValidatorInterface;

/**
 * The FilterRulesTest class.
 */
class FilterRulesTest extends TestCase
{
    /**
     * testFilter
     *
     * @param  callable|string  $filter
     * @param  mixed            $value
     * @param  mixed            $expected
     * @param  string           $message
     *
     * @return  void
     *
     * @dataProvider provideFilter
     */
    public function testFilter(string|callable $filter, $value, $expected, string $message = '')
    {
        if (is_string($filter)) {
            $filter = fn() => new $filter();
        }

        /** @var FilterInterface $filter */
        $filter = $filter();

        self::assertSame(
            $expected,
            $filter->filter($value),
            $message
        );
    }

    public function provideFilter(): array
    {
        return [
            [
                Absolute::class,
                -234,
                234,
            ],
            [
                Alnum::class,
                'This is alpha with 123 中文測試',
                'Thisisalphawith123',
            ],
            [
                fn() => new CastTo('int'),
                'This is alpha with 123 中文測試',
                0,
                'Cast to int failure',
            ],
            [
                fn() => new CastTo('int'),
                '123.56',
                123,
                'Cast to int success',
            ],
            [
                Cmd::class,
                'php qwe\'dsf.php "foo" -t hello/welcome.pwd',
                'phpqwedsf.phpfoo-thellowelcome.pwd',
                'Command string',
            ],
            [
                fn() => new CompareWith(500, '<'),
                700,
                true,
                'Compare with operator',
            ],
            [
                fn() => new CompareWith(500),
                700,
                -1,
                'Compare without operator',
            ],
            [
                EmailAddress::class,
                'qwe.qwe @gmail .com',
                'qwe.qwe@gmail.com',
                'Test email',
            ],
            [
                IPV4::class,
                'd140. 12/3. b567. 890',
                '140.123.567.890',
                'Test IP',
            ],
            [
                fn() => new Length(10),
                'Some string longer than 10',
                'Some strin',
                'Test length',
            ],
            [
                Negative::class,
                123,
                -123,
                'Test negative',
            ],
            [
                fn() => new Range(200, 300),
                123,
                200,
                'Test range',
            ],
            [
                Words::class,
                'This is a <span>HTML</span>',
                'ThisisaspanHTMLspan',
                'Test word',
            ],
            [
                UrlAddress::class,
                'This is "http://www.domain.com:8000/hello/foo/bar.html" URL.',
                'Thisis"http://www.domain.com:8000/hello/foo/bar.html"URL.',
                'Test URL with string',
            ],
        ];
    }

    /**
     * testFilter
     *
     * @param  callable|string  $filter
     * @param  mixed            $value
     * @param  bool             $expected
     * @param  string           $message
     *
     * @return  void
     *
     * @dataProvider provideValidate
     */
    public function testValidate(string|callable $filter, $value, $expected, string $message = '')
    {
        if (is_string($filter)) {
            $filter = fn() => new $filter();
        }

        /** @var ValidatorInterface $filter */
        $filter = $filter();

        self::assertSame(
            $expected,
            $filter->test($value),
            $message
        );
    }

    public function provideValidate(): array
    {
        return [
            'Absolute F' => [
                Absolute::class,
                -234,
                false,
            ],
            'Absolute T' => [
                Absolute::class,
                234,
                true,
            ],
            'Alnum F' => [
                Alnum::class,
                'This is alpha with 123 中文測試',
                false,
            ],
            'Alnum T' => [
                Alnum::class,
                'This123',
                true,
            ],
            'Cast F' => [
                fn() => new CastTo('int'),
                'This is alpha with 123 中文測試',
                false,
            ],
            'Cast T' => [
                fn() => new CastTo('int'),
                123,
                true,
            ],
            'Cmd F' => [
                Cmd::class,
                'ls -al',
                false,
            ],
            'Cmd T' => [
                Cmd::class,
                'ls',
                true,
            ],
            'Compare F' => [
                fn() => new CompareWith(500, '<'),
                300,
                false,
                'Compare with operator',
            ],
            'Compare T' => [
                fn() => new CompareWith(500, '<'),
                700,
                true,
                'Compare with operator',
            ],
            'Compare without operator F' => [
                fn() => new CompareWith(500),
                700,
                false,
                'Compare without operator',
            ],
            'Email F' => [
                EmailAddress::class,
                'qwe.qwe @gmail .com',
                false,
                'Test email',
            ],
            'Email T' => [
                EmailAddress::class,
                'qwe.qwe@gmail.com',
                true,
                'Test email',
            ],
            'IPV4' => [
                IPV4::class,
                'd140. 12/3. b567. 890',
                false,
                'Test IP',
            ],
            'Length' => [
                fn() => new Length(10),
                'Some string longer than 10',
                false,
                'Test length',
            ],
            'Negative' => [
                Negative::class,
                -123,
                true,
                'Test negative',
            ],
            'Range' => [
                fn() => new Range(200, 300),
                123,
                false,
                'Test range',
            ],
            'Word' => [
                Words::class,
                'This is a <span>HTML</span>',
                false,
                'Test word',
            ],
            [
                UrlAddress::class,
                'http://www.domain.com:8000/hello/foo/bar.html',
                true,
                'Test URL',
            ],
            [
                UrlAddress::class,
                'This is "http://www.domain.com:8000/hello/foo/bar.html" URL.',
                false,
                'Test URL with string',
            ],
        ];
    }
}
