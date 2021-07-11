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
     * The component name hash stack.
     *
     * @var array
     */
    protected static array $componentHashStack = [];

    /**
     * Compile the component statements into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileComponent(string $expression): string
    {
        [$component, $name, $data] = Arr::explodeAndClear(',', $this->stripParentheses($expression), 3) + ['', '', ''];

        $component = trim($component, '\'"');

        $hash = static::newComponentHash($component);

        if (Str::contains($component, '::class') || class_exists($component)) {
            return static::compileClassComponentOpening($component, $name, $data, $hash);
        }

        return "<?php \$__edge->startComponent{$expression}; ?>";
    }

    /**
     * Get a new component hash for a component name.
     *
     * @param  string  $component
     *
     * @return string
     */
    public static function newComponentHash(string $component): string
    {
        static::$componentHashStack[] = $hash = sha1($component);

        return $hash;
    }

    /**
     * Compile a class component opening.
     *
     * @param  string  $component
     * @param  string  $name
     * @param  string  $data
     * @param  string  $hash
     *
     * @return string
     */
    public static function compileClassComponentOpening(string $component, string $name, string $data, string $hash)
    {
        $component = Str::ensureRight($component, '::class');

        return implode(
            "\n",
            [
                '<?php if (isset($component)) { $__componentOriginal' . $hash . ' = $component; } ?>',
                '<?php $component = $__edge->make(' . $component . ', ' . ($data ?: '[]') . '); ?>',
                '<?php $component->withName(' . $name . '); ?>',
                '<?php if ($component->shouldRender()): ?>',
                '<?php $__edge->startComponent($component->resolveView(), $component->data()); ?>',
            ]
        );
    }

    /**
     * Compile the end-component statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndComponent(): string
    {
        $hash = array_pop(static::$componentHashStack);

        return implode(
            "\n",
            [
                '<?php if (isset($__componentOriginal' . $hash . ')): ?>',
                '<?php $component = $__componentOriginal' . $hash . '; ?>',
                '<?php unset($__componentOriginal' . $hash . '); ?>',
                '<?php endif ?>',
                '<?php echo $__edge->renderComponent(); ?>',
            ]
        );
    }

    /**
     * Compile the end-component statements into valid PHP.
     *
     * @return string
     */
    public function compileEndComponentClass(): string
    {
        return $this->compileEndComponent() . "\n" .
            implode(
                "\n",
                [
                    '<?php endif ?>',
                ]
            );
    }

    /**
     * Compile the prop statement into valid PHP.
     *
     * @param  string  $expression
     *
     * @return string
     */
    protected function compileProps(string $expression): string
    {
        return "<?php \$attributes = \$attributes->exceptProps{$expression}; ?>
<?php foreach (array_filter({$expression}, 'is_string', ARRAY_FILTER_USE_KEY) as \$__key => \$__value) {
    \$\$__key = \$\$__key ?? \$__value;
} ?>
<?php \$__defined_vars = get_defined_vars(); ?>
<?php foreach (\$attributes as \$__key => \$__value) {
    if (array_key_exists(\$__key, \$__defined_vars)) unset(\$\$__key);
} ?>
<?php unset(\$__defined_vars); ?>";
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
            $slots = "(function (...\$__scope) use (\$__edge, \$__data) { extract(\$__data);";
        }

        return "<?php \$__data = get_defined_vars(); \$__edge->slot({$expression})$slots ?>";
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

    /**
     * Sanitize the given component attribute value.
     *
     * @param  mixed  $value
     *
     * @return mixed
     */
    public static function sanitizeComponentAttribute(mixed $value): mixed
    {
        // todo: Must escape stringable
        return is_string($value)
            // || (is_object($value) && !$value instanceof ComponentAttributes && method_exists($value, '__toString'))
            ? e($value)
            : $value;
    }
}
