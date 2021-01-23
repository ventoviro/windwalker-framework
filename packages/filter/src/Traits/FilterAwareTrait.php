<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Traits;

use Windwalker\Filter\FilterFactory;

/**
 * The FilterAwareTrait class.
 */
trait FilterAwareTrait
{
    protected ?FilterFactory $filterFactory = null;

    /**
     * @return FilterFactory
     */
    public function getFilterFactory(): FilterFactory
    {
        return $this->filterFactory ??= new FilterFactory();
    }

    /**
     * @param  FilterFactory|null  $filterFactory
     *
     * @return  static  Return self to support chaining.
     */
    public function setFilterFactory(?FilterFactory $filterFactory): static
    {
        $this->filterFactory = $filterFactory;

        return $this;
    }
}
