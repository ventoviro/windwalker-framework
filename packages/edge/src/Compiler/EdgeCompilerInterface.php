<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Compiler;

/**
 * The CompilerInterface class.
 *
 * @since  3.0
 */
interface EdgeCompilerInterface
{
    /**
     * compile
     *
     * @param  string  $value
     *
     * @return  string
     */
    public function compile(string $value): string;

    /**
     * Register a handler for custom directives.
     *
     * @param  string    $name
     * @param  callable  $handler
     *
     * @return static
     */
    public function directive(string $name, callable $handler): static;

    /**
     * parser
     *
     * @param  callable  $handler
     *
     * @return static
     */
    public function parser(callable $handler): static;
}
