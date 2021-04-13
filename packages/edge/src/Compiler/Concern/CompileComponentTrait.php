<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Compiler\Concern;

use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Str;

/**
 * The CompileComponentTrait class.
 *
 * @since  3.3.1
 */
trait CompileComponentTrait
{
    /**
     * Compile the component statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileComponent(string $expression): string
    {
        return "<?php \$__edge->startComponent{$expression}; ?>";
    }

    /**
     * Compile the end-component statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndComponent(): string
    {
        return '<?php echo $__edge->renderComponent(); ?>';
    }

    /**
     * Compile the slot statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileSlot(string $expression): string
    {
        $expression = $this->stripParentheses($expression);
        $expr = Arr::explodeAndClear(',', $expression);

        $slots = ';';

        if (
            count($expr) <= 1
            && strtolower($expr[0] ?? '') !== 'null'
        ) {
            $slots = "(function (...\$__scope) use (\$__edge, \$__data) {";
        }

        return "<?php \$__edge->slot({$expression})$slots ?>";
    }

    protected function compileScope(string $expression): string
    {
        $expression = $this->stripParentheses($expression);

        $expr = Arr::explodeAndClear(',', $expression);

        $extract = '';

        // Use: @scope(['foo' => $foo])
        if (count($expr) === 1 && str_starts_with($expr[0], '[')) {
            $extract = "{$expr[0]} = \$__scope; ";
        }

        // Use: @scope($foo, $bar)
        if (count($expr) > 0) {
            $destruct = [];

            foreach ($expr as $var) {
                $varName = Str::removeLeft($var, '$');

                $destruct[] = "'$varName' => $var";
            }

            $extract = '[' . implode(', ', $destruct) . '] = $__scope; ';
        }

        return "<?php $extract ?>";
    }

    /**
     * Compile the end-slot statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndSlot(): string
    {
        return '<?php }); $__edge->endSlot(); ?>';
    }
}
