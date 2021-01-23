<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Wrapper;

/**
 * Interface WrapperInterface
 *
 * @since  __DEPLOY_VERSION__
 */
interface WrapperInterface
{
    /**
     * Get wrapped value.
     *
     * @param  mixed  $src
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __invoke($src): mixed;
}
