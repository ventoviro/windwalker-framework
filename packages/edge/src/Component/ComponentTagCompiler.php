<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Edge\Component;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Windwalker\Attributes\AttributesAccessor;
use Windwalker\Data\Collection;
use Windwalker\Edge\Edge;
use Windwalker\Utilities\Attributes\Prop;
use Windwalker\Utilities\Str;
use Windwalker\Utilities\StrNormalize;
use Windwalker\Utilities\Wrapper\RawWrapper;

use function Windwalker\collect;
use function Windwalker\raw;

/**
 * The ComponentTagCompiler class.
 */
class ComponentTagCompiler
{
    /**
     * The "bind:" attributes that have been compiled for the current component.
     *
     * @var array
     */
    protected array $boundAttributes = [];

    /**
     * ComponentTagCompiler constructor.
     *
     * @param  Edge   $edge
     * @param  array  $components
     */
    public function __construct(
        protected Edge $edge,
        protected array $components = []
    ) {
        //
    }

    /**
     * Compile the component and slot tags within the given string.
     *
     * @param  string  $value
     *
     * @return string
     */
    public function compile(string $value): string
    {
        $value = $this->compileSlots($value);

        return $this->compileTags($value);
    }

    /**
     * Compile the tags within the given string.
     *
     * @param  string  $value
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function compileTags(string $value): string
    {
        $value = $this->compileSelfClosingTags($value);
        $value = $this->compileOpeningTags($value);
        $value = $this->compileClosingTags($value);

        return $value;
    }

    /**
     * Compile the opening tags within the given string.
     *
     * @param  string  $value
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function compileOpeningTags(string $value): string
    {
        $pattern = "/
            <
                \s*
                x[-\:]([\w\-\:\.]*)
                (?<attributes>
                    (?:
                        \s+
                        (?:
                            (?:
                                \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                            )
                            |
                            (?:
                                [\w\-:.@]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \'[^\']*\'
                                        |
                                        [^\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \s*
                )
                (?<![\/=\-])
            >
        /x";

        return preg_replace_callback(
            $pattern,
            function (array $matches) {
                $this->boundAttributes = [];

                $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

                return $this->componentString($matches[1], $attributes);
            },
            $value
        );
    }

    /**
     * Compile the self-closing tags within the given string.
     *
     * @param  string  $value
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function compileSelfClosingTags(string $value): string
    {
        $pattern = "/
            <
                \s*
                x[-\:]([\w\-\:\.]*)
                \s*
                (?<attributes>
                    (?:
                        \s+
                        (?:
                            (?:
                                \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                            )
                            |
                            (?:
                                [\w\-:.@]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \'[^\']*\'
                                        |
                                        [^\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \s*
                )
            \/>
        /x";

        return preg_replace_callback(
            $pattern,
            function (array $matches) {
                $this->boundAttributes = [];

                $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

                return $this->componentString($matches[1], $attributes) . "\n@endComponentClass";
            },
            $value
        );
    }

    /**
     * Compile the Blade component string for the given component and attributes.
     *
     * @param  string  $component
     * @param  array   $attributes
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function componentString(string $component, array $attributes): string
    {
        $class = $this->componentClass($component);

        [$data, $attributes] = $this->partitionDataAndAttributes($class, $attributes);

        $data = $data->mapWithKeys(
            function ($value, $key) {
                return [StrNormalize::toCamelCase($key) => $value];
            }
        );

        // If the component doesn't exists as a class we'll assume it's a class-less
        // component and pass the component as a view parameter to the data so it
        // can be accessed within the component and we can render out the view.
        if (!class_exists($class)) {
            $parameters = [
                'view' => "'$class'",
                'data' => '[' . $this->attributesToString($data->dump(), escapeBound: false) . ']',
            ];

            $class = AnonymousComponent::class;
        } elseif (is_a($class, DynamicComponent::class, true)) {
            $data['edge'] = raw('$__edge');

            $parameters = $data->dump();
        } else {
            $parameters = $data->dump();
        }

        // Bind attributes
        if (isset($attributes[''])) {
            $attributes['attributes'] = $attributes[''];

            unset($attributes['']);
        }

        return "@component('{$class}', '{$component}', [" .
            $this->attributesToString(
                $parameters,
                false
            ) . '])
<?php $component->withAttributes([' . $this->attributesToString(
                $attributes->dump(),
                $class !== DynamicComponent::class
            ) . "]); ?>";
    }

    /**
     * Get the component class for a given component alias.
     *
     * @param  string  $component
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function componentClass(string $component): string
    {
        $loader = $this->edge->getLoader();

        if (isset($this->components[$component])) {
            $class = $this->components[$component];

            if (class_exists($class)) {
                return $class;
            }

            // Component Alias
            if ($loader->has($class)) {
                return $class;
            }

            if ($loader->has($component)) {
                return $component;
            }

            throw new InvalidArgumentException(
                "Unable to locate class or view [{$class}] for component [{$component}]."
            );
        }

        if ($loader->has($component)) {
            return $component;
        }

        throw new InvalidArgumentException(
            "Unable to locate a class or view for component [{$component}]."
        );
    }

    /**
     * Partition the data and extra attributes from the given array of attributes.
     *
     * @param  string  $class
     * @param  array   $attributes
     *
     * @return array<Collection>
     */
    public function partitionDataAndAttributes(string $class, array $attributes)
    {
        // If the class doesn't exists, we'll assume it's a class-less component and
        // return all of the attributes as both data and attributes since we have
        // now way to partition them. The user can exclude attributes manually.
        if (!class_exists($class)) {
            return [collect($attributes), collect($attributes)];
        }

        $properties = (new ReflectionClass($class))->getProperties();
        $props = [];

        foreach ($properties as $property) {
            AttributesAccessor::runAttributeIfExists(
                $property,
                Prop::class,
                function ($prop, ReflectionProperty $property) use (&$attributes, &$props) {
                    $propName = StrNormalize::toKebabCase($property->getName());

                    if ($attributes[$propName] ?? null) {
                        $props[$property->getName()] = $attributes[$propName];

                        unset($attributes[$propName]);
                    }
                }
            );
        }

        return [collect($props), collect($attributes)];
    }

    /**
     * Compile the closing tags within the given string.
     *
     * @param  string  $value
     *
     * @return string
     */
    protected function compileClosingTags(string $value): string
    {
        return preg_replace("/<\/\s*x[-\:][\w\-\:\.]*\s*>/", ' @endComponentClass', $value);
    }

    /**
     * Compile the slot tags within the given string.
     *
     * @param  string  $value
     *
     * @return string
     */
    public function compileSlots(string $value): string
    {
        $value = preg_replace_callback(
            '/<\s*x[\-\:]slot\s+(:?)name=(?<name>(\"[^\"]+\"|\\\'[^\\\']+\\\'|[^\s>]+))\s*>/',
            function ($matches) {
                $name = $this->stripQuotes($matches['name']);

                if ($matches[1] !== ':') {
                    $name = "'{$name}'";
                }

                return " @slot({$name}) ";
            },
            $value
        );

        return preg_replace('/<\/\s*x[\-\:]slot[^>]*>/', ' @endslot', $value);
    }

    /**
     * Get an array of attributes from the given attribute string.
     *
     * @param  string  $attributeString
     *
     * @return array
     */
    protected function getAttributesFromAttributeString(string $attributeString): array
    {
        $attributeString = $this->parseAttributeBag($attributeString);

        $attributeString = $this->parseBindAttributes($attributeString);

        $pattern = '/
            (?<attribute>[\w\-:.@]+)
            (
                =
                (?<value>
                    (
                        \"[^\"]+\"
                        |
                        \\\'[^\\\']+\\\'
                        |
                        [^\s>]+
                    )
                )
            )?
        /x';

        if (!preg_match_all($pattern, $attributeString, $matches, PREG_SET_ORDER)) {
            return [];
        }

        return collect($matches)->mapWithKeys(
            function ($match) {
                $attribute = $match['attribute'];
                $value = $match['value'] ?? null;

                if (is_null($value)) {
                    $value = 'true';

                    $attribute = Str::ensureLeft($attribute, 'bind:');
                }

                $value = $this->stripQuotes($value);

                if (Str::startsWith($attribute, 'bind:')) {
                    $attribute = Str::removeLeft($attribute, 'bind:');

                    $this->boundAttributes[$attribute] = true;
                } else {
                    $value = "'" . $this->compileAttributeEchos($value) . "'";
                }

                if (Str::startsWith($attribute, '::')) {
                    $attribute = substr($attribute, 1);
                }

                return [$attribute => $value];
            }
        )->dump();
    }

    /**
     * Parse the attribute bag in a given attribute string into its fully-qualified syntax.
     *
     * @param  string  $attributeString
     *
     * @return string
     */
    protected function parseAttributeBag(string $attributeString)
    {
        $pattern = "/
            (?:^|\s+)                                        # start of the string or whitespace between attributes
            \{\{\s*(\\\$attributes(?:[^}]+?(?<!\s))?)\s*\}\} # exact match of attributes variable being echoed
        /x";

        return preg_replace($pattern, ' :attributes="$1"', $attributeString);
    }

    /**
     * Parse the "bind" attributes in a given attribute string into their fully-qualified syntax.
     *
     * @param  string  $attributeString
     *
     * @return string
     */
    protected function parseBindAttributes(string $attributeString)
    {
        $pattern = "/
            (?:^|\s+)     # start of the string or whitespace between attributes
            :(?!:)        # attribute needs to start with a single colon
            ([\w\-:.@]*)  # match the actual attribute name
            =             # only match attributes that have a value
        /xm";

        return preg_replace($pattern, ' bind:$1=', $attributeString);
    }

    /**
     * Compile any Blade echo statements that are present in the attribute string.
     *
     * These echo statements need to be converted to string concatenation statements.
     *
     * @param  string  $attributeString
     *
     * @return string
     */
    protected function compileAttributeEchos(string $attributeString): string
    {
        $value = $this->edge->getCompiler()?->compileEchos($attributeString);

        $value = $this->escapeSingleQuotesOutsideOfPhpBlocks($value);

        $value = str_replace(
            ['<?php echo ', '; ?>'],
            ['\'.', '.\''],
            $value
        );

        return $value;
    }

    /**
     * Escape the single quotes in the given string that are outside of PHP blocks.
     *
     * @param  string  $value
     *
     * @return string
     */
    protected function escapeSingleQuotesOutsideOfPhpBlocks(string $value): string
    {
        return (string) collect(token_get_all($value))
            ->map(
                function ($token) {
                    if (!is_array($token)) {
                        return $token;
                    }

                    return $token[0] === T_INLINE_HTML
                        ? str_replace("'", "\\'", $token[1])
                        : $token[1];
                }
            )
            ->implode('');
    }

    /**
     * Convert an array of attributes to a string.
     *
     * @param  array  $attributes
     * @param  bool   $escapeBound
     *
     * @return string
     */
    protected function attributesToString(array $attributes, bool $escapeBound = true): string
    {
        return (string) collect($attributes)
            ->walk(
                function (string &$value, string $attribute) use ($escapeBound) {
                    if ($value instanceof RawWrapper) {
                        $value = $value();

                        return;
                    }

                    $value = $escapeBound
                    && isset($this->boundAttributes[$attribute])
                    && $value !== 'true'
                    // phpcs:disable
                    && !is_numeric($value)
                        ? "'{$attribute}' => \Windwalker\Edge\Compiler\EdgeCompiler::sanitizeComponentAttribute({$value})"
                        : "'{$attribute}' => {$value}";
                    // phpcs:enable
                }
            )
            ->implode(',');
    }

    /**
     * Strip any quotes from the given string.
     *
     * @param  string  $value
     *
     * @return string
     */
    public function stripQuotes(string $value): string
    {
        return Str::startsWith($value, '"') || Str::startsWith($value, '\'')
            ? substr($value, 1, -1)
            : $value;
    }
}
