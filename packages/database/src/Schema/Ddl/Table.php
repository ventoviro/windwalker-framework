<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Schema\Ddl;

/**
 * The Table class.
 */
class Table
{
    use WrappableTrait;

    public ?string $tableName = null;
    public ?string $tableSchema = null;
    public ?string $tableType = null;
    public ?string $viewDefinition = null;
    public ?string $checkOption = null;
    public ?string $isUpdatable = null;
}
