<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Mysqli;

use JetBrains\PhpStorm\Pure;
use Windwalker\Data\Collection;
use Windwalker\Database\Driver\AbstractStatement;
use Windwalker\Database\Driver\ConnectionInterface;
use Windwalker\Database\Driver\DriverInterface;
use Windwalker\Database\Exception\StatementException;
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Bounded\ParamType;

use function Windwalker\collect;

/**
 * The MysqliStatement class.
 */
class MysqliStatement extends AbstractStatement
{
    /**
     * @var \mysqli_stmt
     */
    protected mixed $cursor;

    /**
     * @var \mysqli_result|null
     */
    protected ?\mysqli_result $result = null;

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
                        'dataType' => ParamType::guessType($param)
                    ];
                },
                $params
            );
        } else {
            $params = $this->bounded;
        }

        [$query, $params] = BoundedHelper::replaceParams($this->query, '?', $params);

        $this->driver->useConnection(function (ConnectionInterface $conn) use ($params, $query) {
            $this->cursor = $stmt = $conn->get()->prepare($query);

            if ($params !== []) {
                $types = '';
                $args = [];

                foreach ($params as $param) {
                    $type = $param['dataType'] ?? ParamType::guessType($param['value']);

                    $types .= ParamType::convertToMysqli($type);
                    $args[] = &$param['value'];
                }

                $stmt->bind_param(
                    $types,
                    ...$args
                );
            }

            $stmt->execute();

            $this->result = $stmt->get_result();
        });

        return true;
    }

    /**
     * @inheritDoc
     */
    public function fetch(array $args = []): ?Collection
    {
        $this->execute();

        if (!$this->result) {
            return null;
        }

        $row = $this->result->fetch_assoc();

        return $row ? collect($row) : null;
    }

    /**
     * @inheritDoc
     */
    public function close(): static
    {
        if ($this->cursor) {
            $this->cursor->free_result();
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

        return $this->cursor->affected_rows;
    }
}
