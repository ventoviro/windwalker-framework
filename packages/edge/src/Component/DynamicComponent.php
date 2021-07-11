<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Edge\Component;

use Closure;
use LogicException;
use Windwalker\Edge\Edge;
use Windwalker\Utilities\Attributes\Prop;
use Windwalker\Utilities\StrNormalize;

use function Windwalker\collect;

/**
 * The DynamicComponent class.
 */
class DynamicComponent extends AbstractComponent
{
    /**
     * The name of the component.
     *
     * @var string
     */
    #[Prop]
    public string $is = '';

    #[Prop]
    public Edge $edge;

    protected ?ComponentTagCompiler $compiler = null;

    /**
     * The cached component classes.
     *
     * @var array
     */
    protected static array $componentClasses = [];

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|string
     */
    public function render(): Closure|string
    {
        // phpcs:disable
        $template = <<<'EOF'
<?php extract(\Windwalker\collect($attributes->getAttributes())->mapWithKeys(function ($value, $key) { return [Windwalker\Utilities\StrNormalize::toCamelCase(str_replace([':', '.'], ' ', $key)) => $value]; })->dump(), EXTR_SKIP); ?>
{{ props }}
<x-{{ is }} {{ bindings }} {{ attributes }}>
{{ slots }}
{{ defaultSlot }}
</x-{{ is }}>
EOF;

        // phpcs:enable

        return function ($edge, $data) use ($template) {
            $bindings = $this->bindings($class = $this->classForComponent());

            return str_replace(
                [
                    '{{ is }}',
                    '{{ props }}',
                    '{{ bindings }}',
                    '{{ attributes }}',
                    '{{ slots }}',
                    '{{ defaultSlot }}',
                ],
                [
                    $this->is,
                    $this->compileProps($bindings),
                    $this->compileBindings($bindings),
                    class_exists($class) ? '{{ $attributes }}' : '',
                    $this->compileSlots($data['__edge_slots']),
                    '{!! $slot ?? "" !!}',
                ],
                $template
            );
        };
    }

    /**
     * Compile the @props directive for the component.
     *
     * @param  array  $bindings
     *
     * @return string
     */
    protected function compileProps(array $bindings): string
    {
        if (empty($bindings)) {
            return '';
        }

        return '@props([\'' .
            implode(
                '\',\'',
                collect($bindings)->map(
                    function ($dataKey) {
                        return StrNormalize::toCamelCase($dataKey);
                    }
                )
                    ->dump()
            ) . '\'])';
    }

    /**
     * Compile the bindings for the component.
     *
     * @param  array  $bindings
     *
     * @return string
     */
    protected function compileBindings(array $bindings): string
    {
        return (string) collect($bindings)
            ->map(
                function ($key) {
                    return ':' . $key . '="$' . StrNormalize::toCamelCase(str_replace([':', '.'], ' ', $key)) . '"';
                }
            )
            ->implode(' ');
    }

    /**
     * Compile the slots for the component.
     *
     * @param  array  $slots
     *
     * @return string
     */
    protected function compileSlots(array $slots): string
    {
        return (string) collect($slots)
            ->walk(
                function (&$slot, $name) {
                    $slot = $name === '__default' ? null : '<x-slot name="' . $name . '">{{ $' . $name . ' }}</x-slot>';
                }
            )
            ->filter()
            ->implode(PHP_EOL);
    }

    /**
     * Get the class for the current component.
     *
     * @return string
     */
    protected function classForComponent(): string
    {
        if (isset(static::$componentClasses[$this->is])) {
            return static::$componentClasses[$this->is];
        }

        return static::$componentClasses[$this->is] =
            $this->compiler()->componentClass($this->is);
    }

    /**
     * Get the names of the variables that should be bound to the component.
     *
     * @param  string  $class
     *
     * @return array
     */
    protected function bindings(string $class): array
    {
        [$data, $attributes] = $this->compiler()->partitionDataAndAttributes(
            $class,
            $this->attributes->getAttributes()
        );

        return array_keys($data->dump());
    }

    /**
     * Get an instance of the Blade tag compiler.
     *
     * @return ComponentTagCompiler
     */
    protected function compiler(): ComponentTagCompiler
    {
        return $this->compiler ??= $this->createCompiler();
    }

    protected function createCompiler(): ComponentTagCompiler
    {
        $extension = $this->edge->getExtension('edge-component');

        if (!$extension) {
            foreach ($this->edge->getExtensions() as $extension) {
                if ($extension instanceof ComponentExtension) {
                    break;
                }
            }
        }

        if (!$extension) {
            throw new LogicException(
                sprintf(
                    'Extension: %s not found.',
                    ComponentExtension::class
                )
            );
        }

        return new ComponentTagCompiler($this->edge, $extension->getComponents());
    }
}
