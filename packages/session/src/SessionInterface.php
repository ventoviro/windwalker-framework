<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session;

use Countable;
use JsonSerializable;
use Windwalker\Utilities\Contract\AccessorAccessibleInterface;

/**
 * Interface SessionInterface
 */
interface SessionInterface extends Countable, JsonSerializable, AccessorAccessibleInterface
{
    public const OPTION_AUTO_COMMIT = 'auto_commit';

    public function clear(): bool;

    public function isStarted(): bool;
}
