<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Compiler\Concern;

/**
 * Trait CompileLoopTrait
 */
trait CompileLoopTrait
{
    /**
     * Compile the for statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileFor(string $expression): string
    {
        return "<?php for{$expression}: ?>";
    }

    /**
     * Compile the foreach statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileForeach(string $expression): string
    {
        return "<?php foreach{$expression}: ?>";
    }

    /**
     * Compile the break statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileBreak(string $expression): string
    {
        return $expression ? "<?php if{$expression} break; ?>" : '<?php break; ?>';
    }

    /**
     * Compile the continue statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileContinue(string $expression): string
    {
        return $expression ? "<?php if{$expression} continue; ?>" : '<?php continue; ?>';
    }

    /**
     * Compile the forelse statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileForelse(string $expression): string
    {
        $empty = '$__empty_' . ++$this->forelseCounter;

        return "<?php {$empty} = true; foreach{$expression}: {$empty} = false; ?>";
    }

    /**
     * Compile the while statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileWhile(string $expression): string
    {
        return "<?php while{$expression}: ?>";
    }

    /**
     * Compile the end-while statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileEndwhile(string $expression): string
    {
        return '<?php endwhile ?>';
    }

    /**
     * Compile the end-for statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileEndfor(string $expression): string
    {
        return '<?php endfor ?>';
    }

    /**
     * Compile the end-for-each statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileEndforeach(string $expression): string
    {
        return '<?php endforeach ?>';
    }

    /**
     * Compile the end-for-else statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileEndforelse(string $expression): string
    {
        return '<?php endif ?>';
    }
}
