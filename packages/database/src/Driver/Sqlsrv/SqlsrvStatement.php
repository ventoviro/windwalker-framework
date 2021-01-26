<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Sqlsrv;

use Windwalker\Data\Collection;
use Windwalker\Database\Driver\AbstractStatement;
use Windwalker\Database\Driver\ConnectionInterface;
use Windwalker\Database\Exception\StatementException;
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Bounded\ParamType;

use function Windwalker\collect;

/**
 * The SqlsrvStatement class.
 */
class SqlsrvStatement extends AbstractStatement
{
    /**
     * @inheritDoc
     */
    protected function doExecute(?array $params = null): bool
    {
        if ($params !== null) {
            // Convert array to bounded params
            $params = array_map(
                static function ($param) {
                    return [
                        'value' => $param,
                        'dataType' => ParamType::guessType($param),
                    ];
                },
                $params
            );
        } else {
            $params = $this->bounded;
        }

        [$query, $params] = BoundedHelper::replaceParams($this->query, '?', $params);

        $args = [];

        foreach ($params as $param) {
            $args[] = &$param['value'];
        }

        $this->driver->useConnection(function (ConnectionInterface $conn) use ($args, $query) {
            $resource = $conn->get();

            $this->cursor = sqlsrv_prepare($resource, $query, $args);
        });

        return sqlsrv_execute($this->cursor);
    }

    /**
     * @inheritDoc
     */
    public function fetch(array $args = []): ?Collection
    {
        $this->execute();

        $row = sqlsrv_fetch_array($this->cursor, SQLSRV_FETCH_ASSOC);

        return $row ? collect($row) : null;
    }

    /**
     * @inheritDoc
     */
    public function close(): static
    {
        if ($this->cursor) {
            sqlsrv_free_stmt($this->cursor);
        }

        $this->cursor = null;
        $this->executed = false;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function countAffected(): int
    {
        if (!$this->cursor) {
            throw new StatementException('Cursor not exists or statement closed.');
        }

        return (int) sqlsrv_rows_affected($this->cursor);
    }
}
