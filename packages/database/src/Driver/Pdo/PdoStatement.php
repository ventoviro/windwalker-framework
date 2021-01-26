<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver\Pdo;

use Windwalker\Data\Collection;
use Windwalker\Database\Driver\AbstractStatement;
use Windwalker\Database\Driver\ConnectionInterface;
use Windwalker\Database\Driver\DriverInterface;
use Windwalker\Database\Exception\StatementException;
use Windwalker\Query\Bounded\ParamType;

use function Windwalker\collect;

/**
 * The PdoStatement class.
 *
 * @method \PDOStatement getCursor()
 */
class PdoStatement extends AbstractStatement
{
    /**
     * @var \PDOStatement
     */
    protected mixed $cursor;

    protected array $options = [];

    /**
     * @inheritDoc
     */
    public function __construct(DriverInterface $driver, string $query, array $bounded = [], array $options = [])
    {
        parent::__construct($driver, $query, $bounded);

        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    protected function doExecute(?array $params = null): bool
    {
        $this->driver->useConnection(function (ConnectionInterface $conn) {
            /** @var \PDO $pdo */
            $pdo = $conn->get();

            $this->cursor = $stmt = $pdo->prepare($this->query, $this->options);

            foreach ($this->getBounded() as $key => $bound) {
                $key = is_int($key) ? $key + 1 : $key;

                $stmt->bindParam(
                    $key,
                    $bound['value'],
                    $bound['dataType'] ?? null,
                    $bound['length'] ?? 0,
                    $bound['driverOptions'] ?? null
                );
            }
        });

        return (bool) $this->cursor->execute($params);
    }

    /**
     * @inheritDoc
     */
    public function fetch(array $args = []): ?Collection
    {
        $this->execute();

        $item = $this->cursor->fetch(\PDO::FETCH_ASSOC);

        return $item !== false ? collect($item) : null;
    }

    /**
     * @inheritDoc
     */
    public function close(): static
    {
        if ($this->cursor) {
            $this->cursor->closeCursor();
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

        return $this->cursor->rowCount();
    }
}
