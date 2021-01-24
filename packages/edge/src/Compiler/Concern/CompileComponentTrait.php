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
        return "<?php \$__edge->slot{$expression}; ?>";
    }

    /**
     * Compile the end-slot statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndSlot(): string
    {
        return '<?php $__edge->endSlot(); ?>';
    }
}
