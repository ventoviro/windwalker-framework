<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Compiler\Concern;

use Windwalker\Edge\Edge;

/**
 * Trait CompileLayoutTrait
 */
trait CompileLayoutTrait
{
    /**
     * The name of the last section that was started.
     *
     * @var string
     */
    protected string $lastSection = '';

    /**
     * Compile the extends statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileExtends(string $expression): string
    {
        $expression = $this->stripParentheses($expression);

        // phpcs:disable
        $data = "<?php echo \$__edge->render($expression, \$__edge->except(get_defined_vars(), ['__data', '__path'])); ?>";
        // phpcs:enable

        $this->footer[] = $data;

        return '';
    }

    /**
     * Compile the yield statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileYield(string $expression): string
    {
        return "<?php echo \$__edge->yieldContent{$expression}; ?>";
    }

    /**
     * Compile the show statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileShow(string $expression): string
    {
        return '<?php endif; echo $__edge->yieldSection(); ?>';
    }

    /**
     * Compile the section statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileSection(string $expression): string
    {
        $this->lastSection = trim($expression, "()'\" ");

        $params = explode(',', $expression);

        if (count($params) >= 2) {
            return "<?php \$__edge->startSection{$expression}; ?>";
        }

        return "<?php \$__edge->startSection{$expression}; if (\$__edge->hasParent{$expression}): ?>";
    }

    /**
     * Compile the append statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileAppend(string $expression): string
    {
        return '<?php endif; $__edge->appendSection(); ?>';
    }

    /**
     * Compile the end-section statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileEndsection(string $expression): string
    {
        return '<?php endif; $__edge->stopSection(); ?>';
    }

    /**
     * Compile the stop statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileStop(string $expression): string
    {
        return '<?php endif; $__edge->stopSection(); ?>';
    }

    /**
     * Compile the overwrite statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileOverwrite(string $expression): string
    {
        return '<?php endif; $__edge->stopSection(true); ?>';
    }

    /**
     * Compile the has section statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileHasSection(string $expression): string
    {
        return "<?php if (! empty(trim(\$__edge->yieldContent{$expression}))): ?>";
    }

    /**
     * Replace the @parent directive to a placeholder.
     *
     * @return string
     */
    protected function compileParent(): string
    {
        return Edge::parentPlaceholder($this->lastSection ?: '');
    }
}
