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
 * The CompileIncludeTrait class.
 */
trait CompileIncludeTrait
{
    /**
     * Compile the each statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileEach(string $expression): string
    {
        return "<?php echo \$__edge->renderEach{$expression}; ?>";
    }

    /**
     * Compile the include statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileInclude(string $expression): string
    {
        $expression = $this->stripParentheses($expression);

        // phpcs:disable
        return "<?php echo \$__edge->render($expression, \$__edge->except(get_defined_vars(), ['__data', '__path'])); ?>";
        // phpcs:enable
    }

    /**
     * Compile the include statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileIncludeIf(string $expression): string
    {
        $expression = $this->stripParentheses($expression);

        // phpcs:disable
        return "<?php if (\$__edge->exists($expression)) echo \$__edge->render($expression, \$__edge->except(get_defined_vars(), ['__data', '__path'])); ?>";
        // phpcs:enable
    }
}
