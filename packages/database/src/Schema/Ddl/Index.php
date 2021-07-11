<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Schema\Ddl;

use Windwalker\Database\Platform\Type\DataType;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The Index class.
 */
class Index
{
    use WrappableTrait;
    use OptionAccessTrait;

    public ?string $tableName = null;

    public ?string $indexName = null;

    public ?string $indexComment = null;

    public bool $isUnique;

    public bool $isPrimary;

    /**
     * @var Column[]
     */
    protected array $columns = [];

    /**
     * Index constructor.
     *
     * @param  string|null  $tableName
     * @param  string       $indexName
     */
    public function __construct(?string $indexName = null, ?string $tableName = null)
    {
        $this->tableName = $tableName;
        $this->indexName = $indexName;
    }

    public function tableName(string $tableName): static
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function name(string $indexName): static
    {
        $this->indexName = $indexName;

        return $this;
    }

    public function comment(?string $indexComment): static
    {
        $this->indexComment = $indexComment;

        return $this;
    }

    /**
     * columns
     *
     * @param  Column[]|string[]  $columns
     *
     * @return  $this
     */
    public function columns(array $columns): static
    {
        $cols = [];

        foreach ($columns as $key => $column) {
            if (!$column instanceof Column) {
                if (is_array($column)) {
                    $column = $key;
                }

                [$colName, $subParts] = DataType::extract($column);

                $column = new Column($colName);

                if ($subParts) {
                    $column->erratas(
                        [
                            'sub_parts' => $subParts,
                        ]
                    );
                }
            }

            $cols[$column->getColumnName()] = $column;
        }

        $this->columns = $cols;

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }
}
