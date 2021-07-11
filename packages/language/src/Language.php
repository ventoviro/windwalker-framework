<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Language;

use JetBrains\PhpStorm\ArrayShape;
use Windwalker\Data\Traits\FormatAwareTrait;
use Windwalker\Utilities\Reflection\BacktraceHelper;
use Windwalker\Utilities\Utf8String;

/**
 * Class Language
 *
 * @since 2.0
 */
class Language implements LanguageInterface
{
    use FormatAwareTrait;

    /**
     * Property locale.
     *
     * @var string
     */
    protected ?string $locale = null;

    /**
     * Property defaultLocale.
     *
     * @var  string
     */
    protected ?string $fallback = null;

    /**
     * Property strings.
     *
     * @var  string[]
     */
    protected array $strings = [];

    /**
     * Property used.
     *
     * @var  string[]
     */
    protected array $used = [];

    /**
     * Property orphans.
     *
     * @var  string[]
     */
    protected array $orphans = [];

    /**
     * @var array
     */
    protected array $trace = [];

    /**
     * Property debug.
     *
     * @var  boolean
     */
    protected bool $debug = false;

    protected ?self $parent = null;

    protected ?PluralSelector $selector = null;

    /**
     * Property normalizeHandler.
     *
     * @var  callable
     */
    protected $normalizeHandler = [
        LanguageNormalizer::class,
        'normalize',
    ];

    protected string $namespace;

    /**
     * Constructor.
     *
     * @param  string  $locale
     * @param  string  $fallback
     * @param  string  $namespace
     */
    public function __construct(
        string $locale = 'en-GB',
        string $fallback = 'en-GB',
        string $namespace = ''
    ) {
        $this->setLocale($locale);
        $this->setFallback($fallback);
        $this->namespace = $namespace;
    }

    /**
     * load
     *
     * @param  string       $file
     * @param  string|null  $format
     * @param  string|null  $locale
     * @param  array        $options
     *
     * @return  $this
     */
    public function load(string $file, ?string $format = 'ini', ?string $locale = null, array $options = []): static
    {
        $strings = $this->getFormatRegistry()->load($file, $format, $options);

        $this->addStrings($strings, $locale);

        return $this;
    }

    public function resolveNamespace(string $key): string
    {
        if ($this->namespace) {
            $key = $this->namespace . '.' . $key;
        }

        return $this->normalize($key);
    }

    public function find(string $id, ?string $locale = null, bool $fallback = true): array
    {
        if ($string = $this->strings[$locale][$id] ?? null) {
            return [$locale, $string];
        }

        if ($fallback && isset($this->strings[$fallbackLocale = $this->getFallback()][$id])) {
            // In debug mode, we notice user this is a translating string but not found.
            $string = $this->strings[$fallbackLocale][$id];

            return [$fallbackLocale, $string];
        }

        if ($this->getParent()) {
            return $this->getParent()->find($id, $locale, $fallback);
        }

        return [null, null];
    }

    /**
     * translate
     *
     * @param  string       $id
     * @param  string|null  $locale
     * @param  bool         $fallback
     *
     * @return  array<string>
     * @throws \ReflectionException
     */
    #[ArrayShape(['string', 'string'])]
    public function get(
        string $id,
        ?string $locale = null,
        bool $fallback = true
    ): array {
        $fullId = $this->resolveNamespace($id);
        $locale ??= $this->getLocale();

        $fallback = $fallback || $this->isDebug();

        [$foundLocale, $string] = $this->find($fullId, $locale, $fallback);

        if ($string === null) {
            // In debug mode, we notice user this is a translating string but not found.
            if ($this->isDebug()) {
                $this->orphans[$fullId] = $this->backtrace($fullId);

                $id = '??' . $id . '??';
            }

            return [null, $id];
        }

        // In debug mode, we notice user this is a translated string.
        if ($this->isDebug()) {
            $string = '**' . $string . '**';
        }

        // Store used keys
        if (!in_array($fullId, $this->used, true)) {
            $this->used[] = $fullId;
        }

        return [$foundLocale, $string];
    }

    public function trans(string $id, ...$args): string
    {
        [, $string] = $this->get($id);

        return $this->replace($string, $args);
    }

    public function choice(string $id, int|float $number, ...$args): string
    {
        [$locale, $string] = $this->get($id);

        if (!$locale) {
            return $string;
        }

        $args['count'] = $number;

        return $this->replace(
            $this->getSelector()->choose($string, (int) $number, $locale),
            $args
        );
    }

    public function replace(string $string, array $args = []): string
    {
        if ($string === '') {
            return $string;
        }

        $values = [];
        $replacements = [];

        foreach ($args as $k => $v) {
            if (is_numeric($k)) {
                $values[] = $v;
            } else {
                $replacements[$k] = $v;
            }
        }

        if ($values !== []) {
            $string = sprintf($string, ...$values);
        }

        if ($replacements !== []) {
            $replacements = $this->sortReplacements($replacements);

            foreach ($replacements as $key => $value) {
                $string = str_replace(
                    [
                        ':' . $key,
                        ':' . Utf8String::strtoupper((string) $key),
                        ':' . Utf8String::ucfirst((string) $key),
                    ],
                    [
                        $value,
                        Utf8String::strtoupper((string) $value),
                        Utf8String::ucfirst((string) $value),
                    ],
                    $string
                );
            }
        }

        return $string;
    }

    protected function sortReplacements(array $replacements): array
    {
        uksort($replacements, fn($a, $b) => -(mb_strlen($a) <=> mb_strlen($b)));

        return $replacements;
    }

    /**
     * has
     *
     * @param  string       $id
     * @param  string|null  $locale
     * @param  bool         $fallback
     *
     * @return  bool
     *
     * @since  3.5.2
     */
    public function has(string $id, ?string $locale = null, bool $fallback = true): bool
    {
        [$locale] = $this->get($id, $locale, $fallback);

        return $locale !== null;
    }

    /**
     * addString
     *
     * @param  string       $key
     * @param  string       $string
     * @param  string|null  $locale
     *
     * @return  $this
     */
    public function addString(string $key, string $string, ?string $locale = null): static
    {
        $locale ??= $this->getLocale();

        $this->strings[$locale][$this->resolveNamespace($key)] = $string;

        return $this;
    }

    /**
     * addStrings
     *
     * @param  string[]     $strings
     * @param  string|null  $locale
     *
     * @return  $this
     */
    public function addStrings(array $strings, ?string $locale = null): static
    {
        foreach ($strings as $key => $string) {
            $this->addString($key, $string, $locale);
        }

        return $this;
    }

    /**
     * setDebug
     *
     * @param  boolean  $debug
     *
     * @return  Language  Return self to support chaining.
     */
    public function setDebug(bool $debug): static
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * getOrphans
     *
     * @return  string[]
     */
    public function getOrphans(): array
    {
        return $this->orphans;
    }

    /**
     * getUsed
     *
     * @return  string[]
     */
    public function getUsed(): array
    {
        return $this->used;
    }

    /**
     * getLocale
     *
     * @return  string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * setLocale
     *
     * @param  string  $locale
     *
     * @return  Language  Return self to support chaining.
     */
    public function setLocale(string $locale): static
    {
        $this->locale = LanguageNormalizer::toBCP47($locale);

        return $this;
    }

    /**
     * Method to get property DefaultLocale
     *
     * @return  string
     */
    public function getFallback(): ?string
    {
        return $this->fallback;
    }

    /**
     * Method to set property defaultLocale
     *
     * @param  string  $fallback
     *
     * @return  static  Return self to support chaining.
     */
    public function setFallback(string $fallback): static
    {
        $this->fallback = LanguageNormalizer::toBCP47($fallback);

        return $this;
    }

    /**
     * normalize
     *
     * @param  string  $string
     *
     * @return  mixed
     * @throws \UnexpectedValueException
     */
    public function normalize(string $string): mixed
    {
        $handler = $this->getNormalizeHandler();

        if (!is_callable($handler)) {
            throw new \UnexpectedValueException('Normalize handler is not callable.');
        }

        return $handler($string);
    }

    /**
     * getNormalizeHandler
     *
     * @return  callable
     */
    public function getNormalizeHandler(): array|callable
    {
        return $this->normalizeHandler;
    }

    /**
     * setNormalizeHandler
     *
     * @param  callable  $normalizeHandler
     *
     * @return  Language  Return self to support chaining.
     */
    public function setNormalizeHandler(callable $normalizeHandler): static
    {
        $this->normalizeHandler = $normalizeHandler;

        return $this;
    }

    /**
     * @return PluralSelector
     */
    public function getSelector(): PluralSelector
    {
        return $this->selector ??= new PluralSelector();
    }

    /**
     * @param  PluralSelector|null  $selector
     *
     * @return  static  Return self to support chaining.
     */
    public function setSelector(PluralSelector $selector): static
    {
        $this->selector = $selector;

        return $this;
    }

    /**
     * extract
     *
     * @param  string  $namespace
     *
     * @return  static
     */
    public function extract(string $namespace): static
    {
        $lang = clone $this;
        $lang->setNamespace($namespace);
        $lang->setParent($this);

        return $lang;
    }

    /**
     * backTrace
     *
     * @param  string  $id
     *
     * @return  array
     * @throws \ReflectionException
     */
    protected function backtrace(string $id): array
    {
        $called = BacktraceHelper::findCalled(self::class);
        $caller = BacktraceHelper::findCaller(self::class);

        return $this->trace[$id] ??= compact('called', 'caller');
    }

    /**
     * Method to get property Trace
     *
     * @return  array
     */
    public function getTrace(): array
    {
        return $this->trace;
    }

    /**
     * Method to get property Strings
     *
     * @return  string[]
     *
     * @since  3.5.2
     */
    public function getStrings(): array
    {
        return $this->strings;
    }

    /**
     * Method to set property strings
     *
     * @param  string[]  $strings
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.5.2
     */
    public function setStrings(array $strings): self
    {
        $this->strings = $strings;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param  string  $namespace
     *
     * @return  static  Return self to support chaining.
     */
    public function setNamespace(string $namespace): static
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @return self
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @param  self  $parent
     *
     * @return  static  Return self to support chaining.
     */
    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }
}
