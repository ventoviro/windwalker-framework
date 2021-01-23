<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DOM;

use Windwalker\Utilities\StrNormalise;

/**
 * The DOMStringMap class.
 *
 * @since  3.5.3
 */
class DOMStringMap
{
    /**
     * Property html.
     *
     * @var DOMElement
     */
    protected $element;

    /**
     * ClassList constructor.
     *
     * @param DOMElement $element
     */
    public function __construct(DOMElement $element)
    {
        $this->element = $element;
    }

    /**
     * Method to get property Html
     *
     * @return  DOMElement
     *
     * @since  3.5.3
     */
    public function getDOMElement(): DOMElement
    {
        return $this->element;
    }

    /**
     * getDataAttrs
     *
     * @return  array
     *
     * @since  3.5.3
     */
    protected function getDataAttrs(): array
    {
        $attrs = array_filter(
            $this->element->getAttributes(),
            static function ($v, $k) {
                return strpos($k, 'data-') === 0;
            },
            ARRAY_FILTER_USE_BOTH
        );

        $dataAttrs = [];

        foreach ($attrs as $key => $value) {
            $key = substr($key, 5);

            $dataAttrs[$key] = $value;
        }

        return $dataAttrs;
    }

    /**
     * __get
     *
     * @param string $name
     *
     * @return  string
     *
     * @since  3.5.3
     */
    public function __get($name): string
    {
        return $this->element->getAttribute($this->toDataKey($name));
    }

    /**
     * __set
     *
     * @param string $name
     * @param string $value
     *
     * @return  void
     *
     * @since  3.5.3
     */
    public function __set($name, $value)
    {
        $this->element->setAttribute($this->toDataKey($name), $value);
    }

    /**
     * __isset
     *
     * @param string $name
     *
     * @return  bool
     *
     * @since  3.5.3
     */
    public function __isset($name): bool
    {
        return $this->element->hasAttribute($this->toDataKey($name));
    }

    /**
     * __unset
     *
     * @param string $name
     *
     * @return  void
     *
     * @since  3.5.3
     */
    public function __unset($name)
    {
        $this->element->removeAttribute($this->toDataKey($name));
    }

    /**
     * toDataKey
     *
     * @param string $name
     *
     * @return  string
     *
     * @since  3.5.3
     */
    private function toDataKey(string $name): string
    {
        return 'data-' . StrNormalise::toDashSeparated($name);
    }
}
