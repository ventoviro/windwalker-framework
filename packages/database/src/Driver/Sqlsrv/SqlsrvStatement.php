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
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Bounded\ParamType;

use function Windwalker\collect;

/**
 * The SqlsrvStatement class.
 */
class SqlsrvStatement extends AbstractStatement
{
    /**
     * @var resource
     */
    protected $conn;

    /**
     * @var string
     */
    protected $query;

    /**
     * SqlsrvStatement constructor.
     *
     * @param  resource  $conn
     * @param  string    $query
     * @param  array     $bounded
     */
    public function __construct($conn, string $query, array $bounded = [])
    {
        $this->bounded = $bounded;
        $this->conn = $conn;
        $this->query = $query;
    }

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

        $this->cursor = sqlsrv_prepare($this->conn, $query, $args);

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
        sqlsrv_free_stmt($this->cursor);
        $this->cursor = null;
        $this->executed = false;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function countAffected(): int
    {
        return (int) sqlsrv_rows_affected($this->cursor);
    }
}
