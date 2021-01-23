<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

/**
 * Trait ObjectBuilderAwareTrait
 */
trait ObjectBuilderAwareTrait
{
    protected ?ObjectBuilder $objectBuilder = null;

    /**
     * @return ObjectBuilder
     */
    public function getObjectBuilder(): ObjectBuilder
    {
        return $this->objectBuilder ??= new ObjectBuilder();
    }

    /**
     * @param  ObjectBuilder|null  $objectBuilder
     *
     * @return  static  Return self to support chaining.
     */
    public function setObjectBuilder(?ObjectBuilder $objectBuilder): static
    {
        $this->objectBuilder = $objectBuilder;

        return $this;
    }
}
