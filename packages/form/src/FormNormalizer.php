<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form;

use Windwalker\DOM\DOMElement;

/**
 * The FormNormalizer class.
 */
class FormNormalizer
{
    public static function clearNamespace(string $ns): string
    {
        $nss = explode('/', $ns);
        $ns  = implode('/', array_filter(array_map('trim', $nss), 'strlen'));

        return $ns;
    }

    public static function clearAttribute(string $string): string
    {
        return preg_replace('/[\[\]\s\"\'=\.:\/\\\\]+/', '-', $string);
    }

    public static function sortAttributes(DOMElement $ele, array $firstElements = ['id', 'name']): void
    {
        $attrs = $ele->getAttributes();

        ksort($attrs);

        foreach ($firstElements as $name) {
            if (!isset($attrs[$name])) {
                continue;
            }

            $attr = $attrs[$name];
            unset($attrs[$name]);
            $ele->removeAttribute($name);
            $ele->setAttribute($name, $attr->textContent);
        }

        foreach ($attrs as $name => $attribute) {
            $ele->removeAttribute($name);
            $ele->setAttribute($name, $attribute->textContent);
        }
    }

    /**
     * extractNamespace
     *
     * @param  string  $name
     *
     * @return  string[]
     */
    public static function extractNamespace(string $name): array
    {
        $names = explode('/', static::clearNamespace($name));

        $name = array_pop($names);

        $ns = implode('/', $names);

        return [$ns, $name];
    }
}
