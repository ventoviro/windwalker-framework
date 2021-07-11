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
 * The CompileConditional class.
 *
 * @since  3.5.4
 */
trait CompileConditional
{
    /**
     * Identifier for the first case in switch statement.
     *
     * @var bool
     */
    protected bool $firstCaseInSwitch = true;

    /**
     * Compile the if statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileIf(string $expression): string
    {
        return "<?php if{$expression}: ?>";
    }

    /**
     * Compile the else-if statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileElseif(string $expression): string
    {
        return "<?php elseif{$expression}: ?>";
    }

    /**
     * Compile the forelse statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileEmpty(string $expression): string
    {
        $empty = '$__empty_' . $this->forelseCounter--;

        return "<?php endforeach if ({$empty}): ?>";
    }

    /**
     * Compile the unless statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileUnless(string $expression): string
    {
        return "<?php if ( ! $expression): ?>";
    }

    /**
     * Compile the end unless statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileEndunless(string $expression): string
    {
        return '<?php endif ?>';
    }

    /**
     * Compile the else statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileElse(string $expression): string
    {
        return '<?php else: ?>';
    }

    /**
     * Compile the end-if statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileEndif(string $expression): string
    {
        return '<?php endif ?>';
    }

    /**
     * Compile the switch statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileSwitch(string $expression): string
    {
        $this->firstCaseInSwitch = true;

        return "<?php switch{$expression}:";
    }

    /**
     * Compile the case statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileCase(string $expression): string
    {
        if ($this->firstCaseInSwitch) {
            $this->firstCaseInSwitch = false;

            return "case {$expression}: ?>";
        }

        return "<?php case {$expression}: ?>";
    }

    /**
     * Compile the default statements in switch case into valid PHP.
     *
     * @return string
     */
    protected function compileDefault(): string
    {
        return '<?php default: ?>';
    }

    /**
     * Compile the end switch statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndSwitch(): string
    {
        return '<?php endswitch ?>';
    }
}
