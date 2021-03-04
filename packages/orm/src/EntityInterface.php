<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM;

use Windwalker\Data\Collection;
use Windwalker\Utilities\Contract\DumpableInterface;

/**
 * Interface EntityInterface
 */
interface EntityInterface extends \JsonSerializable, DumpableInterface
{
    public static function table(): ?string;

    public static function newInstance(array $data = []): static;

    public function loadAllRelations(): void;

    public function clearRelations(): void;

    public function toCollection(): Collection;
}
