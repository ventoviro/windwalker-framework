<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pgsql;

use Windwalker\Data\Collection;
use Windwalker\Database\Driver\AbstractStatement;
use Windwalker\Database\Driver\ConnectionInterface;
use Windwalker\Database\Exception\StatementException;
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Bounded\ParamType;

use function Windwalker\collect;

/**
 * The PgsqlStatement class.
 */
class PgsqlStatement extends AbstractStatement
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

        [$query, $params] = BoundedHelper::replaceParams($this->query, '$%d', $params);

        $this->driver->useConnection(function (ConnectionInterface $conn) use ($params, $query) {
            pg_prepare($conn, $stname = uniqid('pg-'), $query);

            $args = [];

            foreach ($params as $param) {
                $args[] = &$param['value'];
            }

            $this->cursor = pg_execute($conn, $stname, $args);
        });

        return true;
    }

    /**
     * @inheritDoc
     */
    public function fetch(array $args = []): ?Collection
    {
        $this->execute();

        $row = pg_fetch_assoc($this->cursor);

        return $row ? collect($row) : null;
    }

    /**
     * @inheritDoc
     */
    public function close(): static
    {
        if ($this->cursor) {
            pg_free_result($this->cursor);
        }

        $this->cursor = null;
        $this->stmt = null;
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

        return pg_affected_rows($this->cursor);
    }
}
