<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Clause;

use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Query\Query;
use Windwalker\Utilities\Classes\FlowControlTrait;

/**
 * The Alter class.
 */
class AlterClause implements ClauseInterface
{
    use FlowControlTrait;

    protected Clause $clause;

    /**
     * @var Query
     */
    protected Query $query;

    /**
     * Alter constructor.
     *
     * @param  Query  $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
        $this->clause = $query->clause('ALTER', [], ",\n");
    }

    public function target(string $target, string $targetName): static
    {
        $this->clause->setName(
            $this->query->format("ALTER %r %n\n", $target, $targetName)
        );

        return $this;
    }

    public function table(string $table): static
    {
        return $this->target('TABLE', $this->query->quoteName($table));
    }

    public function database(string $database): static
    {
        return $this->target('DATABASE', $this->query->quoteName($database));
    }

    public function schema(string $database): static
    {
        return $this->target('SCHEMA', $this->query->quoteName($database));
    }

    public function subClause(string $name, array $elements = []): Clause
    {
        $this->clause->append(
            $clause = $this->query->clause($name, $elements)
        );

        return $clause;
    }

    /**
     * addIndex
     *
     * @param  string    $name
     * @param  string[]  $columns
     *
     * @return  Clause
     */
    public function addIndex(string $name, array $columns = []): Clause
    {
        return $this->subClause('ADD INDEX')
            ->append($this->query->quoteName($name))
            ->append(
                $this->query->clause(
                    '()',
                    $columns,
                    ','
                )
            );
    }

    public function addConstraint(?string $name, string $type, array $columns = []): Clause
    {
        return $this->subClause('ADD CONSTRAINT')
            ->append($name ? $this->query->quoteName($name) : '')
            ->append(
                $this->query->clause(
                    $type . ' ()',
                    $columns,
                    ','
                )
            );
    }

    public function addPrimaryKey(?string $name, array $columns): Clause
    {
        return $this->addConstraint($name, Constraint::TYPE_PRIMARY_KEY, $columns);
    }

    public function addUniqueKey(string $name, array $columns): Clause
    {
        return $this->addConstraint($name, Constraint::TYPE_UNIQUE, $columns);
    }

    public function addForeignKey(
        string $name,
        array $columns,
        array $refColumns,
        ?string $onUpdate,
        ?string $onDelete
    ): Clause {
        $clause = $this->addConstraint($name, Constraint::TYPE_FOREIGN_KEY, $columns)
            ->append(
                $this->query->clause(
                    'REFERENCES ()',
                    $refColumns,
                    ",\n"
                )
            );

        if ($onUpdate) {
            $clause->append(['ON UPDATE', $onUpdate]);
        }

        if ($onDelete) {
            $clause->append(['ON DELETE', $onDelete]);
        }

        return $clause;
    }

    public function modifyColumn(string $columnName, string $expression): Clause
    {
        return $this->subClause(
            '',
            [
                'MODIFY COLUMN',
                $this->query->quoteName($columnName),
                $expression,
            ]
        );
    }

    public function append($elements): static
    {
        $this->clause->append($elements);

        return $this;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->clause;
    }

    /**
     * @return Clause
     */
    public function getClause(): Clause
    {
        return $this->clause;
    }
}
