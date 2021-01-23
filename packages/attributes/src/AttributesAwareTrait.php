<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2020 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Attributes;

/**
 * Trait AttributesAwareTrait
 */
trait AttributesAwareTrait
{
    protected ?AttributesResolver $attributeResolver = null;

    /**
     * @return AttributesResolver
     */
    public function getAttributesResolver(): AttributesResolver
    {
        return $this->attributeResolver ??= new AttributesResolver();
    }

    /**
     * setAttributesResolver
     *
     * @param  AttributesResolver  $attributesResolver
     *
     * @return  static
     */
    public function setAttributesResolver(AttributesResolver $attributesResolver): static
    {
        $this->attributeResolver = $attributesResolver;

        return $this;
    }
}
