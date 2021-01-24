<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DOM;

use ArrayAccess;
use DOMAttr;
use DOMDocument;
use DOMElement as NativeDOMElement;
use DOMNode;
use DOMNodeList;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use LogicException;
use Masterminds\HTML5;
use Symfony\Component\DomCrawler\Crawler;
use Twig\Node\TextNode;
use Windwalker\Utilities\Str;

use function Windwalker\value;

/**
 * Class DomElement
 *
 * @property-read DOMTokenList $classList
 * @property-read DOMTokenList $relList
 * @property-read DOMStringMap $dataset
 *
 * @since 2.0
 */
class DOMElement extends NativeDOMElement implements ArrayAccess
{
    public const HTML = 'html';

    public const XML = 'xml';

    protected string $type = self::HTML;

    /**
     * create
     *
     * @param  string  $name
     * @param  array   $attributes
     * @param  mixed   $content
     *
     * @return  DOMElement
     */
    public static function create(string $name, array $attributes = [], $content = null): DOMElement
    {
        [$name, $id, $class] = array_values(static::splitCSSSelector($name));

        if ($id !== null) {
            $attributes['id'] = $id;
        }

        /** @var static $ele */
        $ele = DOMFactory::element($name)->asXML();

        $ele->setAttributes($attributes);

        if ($class !== null) {
            $ele->addClass($class);
        }

        if ($content !== null) {
            static::insertContentTo($content, $ele);
        }

        return $ele;
    }

    /**
     * valueToString
     *
     * @param  mixed  $value
     *
     * @return  string
     */
    protected static function valueToString(mixed $value): string
    {
        $value = value($value);

        if (is_stringable($value)) {
            return (string) $value;
        }

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_THROW_ON_ERROR);
        }

        return $value;
    }

    /**
     * insertContentTo
     *
     * @param  mixed    $content
     * @param  DOMNode  $node
     *
     * @return  DOMNode
     */
    protected static function insertContentTo(mixed $content, DOMNode $node): DOMNode
    {
        $content = value($content);

        if (is_array($content) || $content instanceof DOMNodeList) {
            $fragment = $node->ownerDocument->createDocumentFragment();

            foreach ($content as $key => $c) {
                static::insertContentTo($c, $fragment);
            }

            return $node->appendChild($fragment);
        }

        if ($content instanceof DOMNode) {
            return $node->appendChild($content);
        }

        $text = $node->ownerDocument->createTextNode((string) $content);

        return $node->appendChild($text);
    }

    /**
     * Adds new child at the end of the children.
     *
     * @param  DOMNode|array|string  $newnode  The appended child.
     *
     * @return DOMNode The node added.
     */
    public function appendChild(mixed $newnode): DOMNode
    {
        if (!$newnode instanceof DOMNode) {
            return self::insertContentTo($newnode, $this);
        }

        if (!$this->ownerDocument->isSameNode($newnode->ownerDocument)) {
            $newnode = $this->ownerDocument->importNode($newnode->cloneNode(true), true);
        }

        return parent::appendChild($newnode);
    }

    public function appendText(string $string): TextNode
    {
        return static::insertContentTo($string, $this);
    }

    /**
     * render
     *
     * @param  string|null  $type
     * @param  bool         $format
     *
     * @return  string
     */
    public function render(?string $type = self::HTML, bool $format = false): string
    {
        $type = $type ?? $this->type;

        $this->ownerDocument->formatOutput = $format;

        if ($type === static::XML) {
            $result = $this->ownerDocument->saveXML($this);
        } elseif (class_exists(HTML5::class)) {
            $result = DOMFactory::html5()->saveHTML($this);
        } else {
            $dom    = HTMLFactory::document();
            $result = $dom->saveHTML($this);
        }

        $this->ownerDocument->formatOutput = false;

        return $result;
    }

    /**
     * Convert this object to string.
     *
     * @return  string
     */
    public function __toString(): string
    {
        return $this->render(null);
    }

    /**
     * getAttributes
     *
     * @param  bool  $toString
     *
     * @return  string[]|DOMAttr[]
     */
    public function getAttributes(bool $toString = false): array
    {
        if ($this->attributes === null) {
            return [];
        }

        $attrs = iterator_to_array($this->attributes);

        if (!$toString) {
            return $attrs;
        }

        return array_map(
            static function (DOMAttr $attr) {
                return $attr->value;
            },
            $attrs
        );
    }

    /**
     * Set all attributes.
     *
     * @param  array  $attribs  All attributes.
     *
     * @return  static  Return self to support chaining.
     */
    public function setAttributes(array $attribs): static
    {
        foreach ($attribs as $key => $attribute) {
            $this->setAttribute($key, $attribute);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAttribute($name, $value): bool|DOMAttr
    {
        if ($value === true) {
            return $this->setAttribute($name, '');
        }

        if ($value === null || $value === false) {
            return $this->ownerDocument->createAttribute($name);
        }

        return parent::setAttribute($name, static::valueToString($value));
    }

    /**
     * querySelectorAll
     *
     * @param  string  $selector
     *
     * @return  Crawler|static[]
     */
    public function querySelectorAll(string $selector): Crawler
    {
        return $this->getCrawler()->filter($selector);
    }

    /**
     * querySelector
     *
     * @param  string  $selector
     *
     * @return  Crawler
     */
    public function querySelector(string $selector): Crawler
    {
        return $this->getCrawler()->filter($selector)->first();
    }

    /**
     * getCrawler
     *
     * @return  Crawler
     */
    public function getCrawler(): Crawler
    {
        if (!class_exists(Crawler::class)) {
            throw new LogicException('Please install symfony/dom-crawler first.');
        }

        return new Crawler($this);
    }

    /**
     * Get element tag name.
     *
     * @return  string
     */
    public function getName(): string
    {
        return $this->tagName;
    }

    /**
     * Whether a offset exists
     *
     * @param  mixed  $offset  An offset to check for.
     *
     * @return bool True on success or false on failure.
     *                 The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->hasAttribute($offset);
    }

    /**
     * Offset to retrieve
     *
     * @param  mixed  $offset  The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * Offset to set
     *
     * @param  mixed  $offset  The offset to assign the value to.
     * @param  mixed  $value   The value to set.
     *
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @param  mixed  $offset  The offset to unset.
     *
     * @return void
     */
    public function offsetUnset(mixed $offset)
    {
        $this->removeAttribute($offset);
    }

    /**
     * Use another root document.
     *
     * @param  DOMNode  $node
     * @param  bool     $deep
     *
     * @return  static|NativeDOMElement
     */
    public function with(DOMNode $node, bool $deep = true): NativeDOMElement|static
    {
        if ($node instanceof DOMDocument) {
            $dom = $node;
        } else {
            $dom = $node->ownerDocument;
        }

        return $dom->importNode($this, $deep);
    }

    /**
     * createChild
     *
     * @param  string  $name
     * @param  array   $attributes
     * @param  mixed   $content
     *
     * @return  static
     */
    public function createChild(string $name, array $attributes = [], $content = null): DOMNode|static
    {
        $ele = static::create($name, $attributes, $content);

        return $this->appendChild($ele);
    }

    /**
     * buildAttributes
     *
     * @param  array        $attributes
     * @param  string|null  $type
     *
     * @return  string
     */
    public static function buildAttributes(array $attributes, ?string $type = null): string
    {
        $ele = static::create('root', $attributes, '')->render($type);

        return Str::removeLeft(Str::removeRight($ele, '></root>'), '<root ');
    }

    /**
     * addClass
     *
     * @param  string|callable  $class
     *
     * @return  static
     *
     * @since  3.5.3
     */
    public function addClass(string $class): static
    {
        $classes = array_filter(explode(' ', $class), 'strlen');

        $this->classList->add(...$classes);

        return $this;
    }

    /**
     * removeClass
     *
     * @param  string|callable  $class
     *
     * @return  static
     *
     * @since  3.5.3
     */
    public function removeClass(string $class): static
    {
        $classes = array_filter(explode(' ', $class), 'strlen');

        $this->classList->remove(...$classes);

        return $this;
    }

    /**
     * toggleClass
     *
     * @param  string     $class
     * @param  bool|null  $force
     *
     * @return  static
     *
     * @since  3.5.3
     */
    public function toggleClass(string $class, ?bool $force = null): static
    {
        $this->classList->toggle($class, $force);

        return $this;
    }

    /**
     * hasClass
     *
     * @param  string  $class
     *
     * @return  static
     *
     * @since  3.5.3
     */
    public function hasClass(string $class): self
    {
        $this->classList->contains($class);

        return $this;
    }

    /**
     * data
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  string|static
     *
     * @since  3.5.3
     */
    public function data(string $name, $value = null): string|static
    {
        if ($value === null) {
            return $this->getAttribute('data-' . $name);
        }

        return $this->setAttribute('data-' . $name, $value);
    }

    /**
     * asXML
     *
     * @return  static
     */
    public function asXML(): static
    {
        $this->type = static::XML;

        return $this;
    }

    /**
     * asHTML
     *
     * @return  static
     */
    public function asHTML(): static
    {
        $this->type = static::HTML;

        return $this;
    }

    /**
     * __get
     *
     * @param  string  $name
     *
     * @return  mixed
     *
     * @since  3.5.3
     */
    public function __get(string $name): mixed
    {
        if ($name === 'dataset') {
            return new DOMStringMap($this);
        }

        if ($name === 'classList') {
            return new DOMTokenList($this, 'class');
        }

        if ($name === 'relList') {
            return new DOMTokenList(
                $this,
                'rel',
                [
                    'alternate',
                    'author',
                    'dns-prefetch',
                    'help',
                    'icon',
                    'license',
                    'next',
                    'pingback',
                    'preconnect',
                    'prefetch',
                    'preload',
                    'prerender',
                    'prev',
                    'search',
                    'stylesheet',
                ]
            );
        }

        return $this->$name;
    }

    #[ArrayShape(['name' => "mixed|string", 'id' => "null|string", 'class' => "null|string"])]
    public static function splitCSSSelector(
        string $name
    ): array {
        $tokens = preg_split(
            '/([\.#]?[^\s#.]+)/',
            $name,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        if ($tokens === []) {
            throw new InvalidArgumentException('Tag name is empty');
        }

        if (!in_array($tokens[0][0] ?? '', ['#', '.'], true)) {
            $name = array_shift($tokens);
        } else {
            $name = 'div';
        }

        $id    = null;
        $class = [];

        foreach ($tokens as $token) {
            if (str_starts_with($token, '#')) {
                $id = ltrim($token, '#');
            } else {
                $class[] = ltrim($token, '.');
            }
        }

        return [
            'name' => $name,
            'id' => $id,
            'class' => $class ? implode(' ', $class) : null,
        ];
    }
}
